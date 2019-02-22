<?php

namespace Webparking\DbRebuild\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Webparking\DbRebuild\Config;

class DbRebuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:rebuild {--preset=default} {--f}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Saves sessions & users table, drops everything, migrates, seeds and optionally devSeeds';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Kernel
     */
    private $artisan;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $backup;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Connection $connection, Kernel $artisan)
    {
        $this->connection = $connection;
        $this->artisan = $artisan;

        parent::__construct();
    }

    public function handle(): void
    {
        if (app()->environment('production')) {
            $this->error('Rebuilding in production is not allowed!');

            return;
        }

        $this->config = new Config($this->option('preset'));

        $this->backupData();

        try {
            $this->migrate();
            $this->callCommands();
            $this->seed();
        } catch (\Exception $e) {
            $this->restoreData();

            throw $e;
        }

        $this->restoreData();
    }

    private function backupData(): void
    {
        $this->info('Backing up data');

        $this->backup = collect();

        foreach ($this->config->getBackup() as $table) {
            if ($this->connection->getSchemaBuilder()->hasTable($table)) {
                $this->info('Backing up ' . $table);
                $this->backup->put($table, $this->connection->table($table)->get());
                continue;
            }

            $this->warn('Table not found: ' . $table);
        }
    }

    private function restoreData(): void
    {
        $this->backup->each(function (Collection $data, string $table) {
            if ($this->connection->getSchemaBuilder()->hasTable($table)) {
                $this->info('Restoring ' . $table);

                $data->each(function ($record) use ($table) {
                    $this->connection->table($table)->insert(get_object_vars($record));
                });

                return;
            }

            $this->error('Table not found: ' . $table);
        });
    }

    private function migrate(): void
    {
        $database = $this->config->getDatabase();

        if ($this->realConfirm(
            "This will drop all tables in {$database}. Are you sure you want to do this? [yes|no]",
            true
        )) {
            $this->connection->statement('SET FOREIGN_KEY_CHECKS=0;');
            $tables = $this->connection->select('SHOW TABLES');

            foreach ($tables as $table) {
                $tableName = $table->{key($table)};
                $this->info('dropping table ' . $tableName);
                $this->connection->getSchemaBuilder()->dropIfExists($tableName);
            }

            $this->connection->statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info("\nAll tables in {$database} dropped!");

            $this->info("\nMigration started");
            $this->artisan->call('migrate');
            $this->info('Migration finished');
        }
    }

    private function callCommands(): void
    {
        foreach ($this->config->getCommands() as $command) {
            $this->info('Calling command: ' . $command);
            $this->artisan->call($command);
        }
    }

    private function seed(): void
    {
        foreach ($this->config->getSeeds() as $seed) {
            $this->info('Calling seeder: ' . $seed);
            $this->artisan->call('db:seed', [
                '--class' => $seed,
            ]);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function realConfirm(string $msg, bool $default = false): bool
    {
        if ($this->option('f')) {
            return true;
        }

        $answer = $this->choice($msg, ['No', 'Yes'], (true === $default) ? '1' : '0');
        switch ($answer) {
            case 'No':
                return false;
            case 'Yes':
                return true;
        }
    }
}
