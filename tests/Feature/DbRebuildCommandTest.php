<?php

namespace Webparking\DbRebuild\Tests\Feature;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Console\Tester\CommandTester;
use Webparking\DbRebuild\Commands\DbRebuild;
use Webparking\DbRebuild\Tests\Database\DatabaseSeeder;
use Webparking\DbRebuild\Tests\TestCase;

class DbRebuildCommandTest extends TestCase
{
    use RefreshDatabase;

    public function testDefault(): void
    {
        $this->setConfig('default', [
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'backup' => [],
            'commands' => [],
            'seeds' => [],
        ]);

        $command = $this->runCommand(app(DbRebuild::class), [], ['Yes']);
        $output = $command->getDisplay();

        $this->assertContains('This will drop all tables in db_rebuild. Are you sure you want to do this? [yes|no]', $output);
        $this->assertContains('All tables in db_rebuild dropped!', $output);
        $this->assertContains('Migration started', $output);
        $this->assertContains('Migration finished', $output);
    }

    public function testForce(): void
    {
        $this->setConfig('default', [
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'backup' => [],
            'commands' => [],
            'seeds' => [],
        ]);

        $command = $this->runCommand(app(DbRebuild::class), ['--f' => true], ['Yes']);
        $output = $command->getDisplay();

        $this->assertNotContains('This will drop all tables in db_rebuild. Are you sure you want to do this? [yes|no]', $output);
        $this->assertContains('All tables in db_rebuild dropped!', $output);
        $this->assertContains('Migration started', $output);
        $this->assertContains('Migration finished', $output);
    }

    public function testSeed(): void
    {
        $this->setConfig('default', [
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'backup' => [],
            'commands' => [],
            'seeds' => [
                DatabaseSeeder::class,
            ],
        ]);

        $command = $this->runCommand(app(DbRebuild::class), [], ['Yes']);
        $output = $command->getDisplay();

        $this->assertContains('Calling seeder: Webparking\\DbRebuild\\Tests\\Database\\DatabaseSeeder', $output);
    }

    public function testCommands(): void
    {
        $this->setConfig('default', [
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'backup' => [],
            'commands' => [
                'view:clear',
            ],
            'seeds' => [],
        ]);

        $command = $this->runCommand(app(DbRebuild::class), [], ['Yes']);
        $output = $command->getDisplay();

        $this->assertContains('Calling command: view:clear', $output);
    }

    public function testBackupWithoutTable(): void
    {
        $this->setConfig('default', [
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'backup' => [
                'non_existing_table',
            ],
            'commands' => [],
            'seeds' => [],
        ]);

        $command = $this->runCommand(app(DbRebuild::class), [], ['Yes']);
        $output = $command->getDisplay();

        $this->assertContains('Table not found: non_existing_table', $output);
    }

    public function testBackup(): void
    {
        /** @var \Illuminate\Contracts\Console\Kernel $artisan */
        $artisan = app(\Illuminate\Contracts\Console\Kernel::class);

        $artisan->call('migrate');

        /** @var \Illuminate\Database\Connection $connection */
        $connection = app(\Illuminate\Database\Connection::class);

        $connection->table('test_table')->insert([
            'id' => 'test',
            'name' => 'test-name',
        ]);

        $this->assertDatabaseHas('test_table', [
            'id' => 'test',
            'name' => 'test-name',
        ]);

        $this->setConfig('default', [
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'backup' => [
                'test_table',
            ],
            'commands' => [],
            'seeds' => [],
        ]);

        $command = $this->runCommand(app(DbRebuild::class), [], ['Yes']);
        $output = $command->getDisplay();

        $this->assertContains('Backing up test_table', $output);
        $this->assertContains('Restoring test_table', $output);

        $this->assertDatabaseHas('test_table', [
            'id' => 'test',
            'name' => 'test-name',
        ]);
    }

    private function setConfig(string $key, array $config): void
    {
        config()->set('db-rebuild.presets.' . $key, $config);
    }

    private function runCommand(Command $command, array $arguments = [], array $interactiveInput = []): CommandTester
    {
        $command->setLaravel(app());

        $tester = new CommandTester($command);
        $tester->setInputs($interactiveInput);

        $tester->execute($arguments);

        return $tester;
    }
}
