<?php

namespace Webparking\DbRebuild\Tests\Feature;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Console\Tester\CommandTester;
use Webparking\DbRebuild\Commands\DbRebuild;
use Webparking\DbRebuild\Tests\TestCase;

class DbRebuildConfigTest extends TestCase
{
    use RefreshDatabase;

    public function testCorrectPreset(): void
    {
        $this->setConfig('default', [
            'database' => 'db_rebuild',
        ]);

        $this->setConfig('test', [
            'database' => 'test_database',
            'backup' => [],
            'commands' => [],
            'seeds' => [],
        ]);

        $command = $this->runCommand(app(DbRebuild::class), ['--preset' => 'test'], ['No']);
        $output = $command->getDisplay();

        $this->assertContains('This will drop all tables in test_database. Are you sure you want to do this? [yes|no]', $output);
    }

    public function testNonExistingPreset(): void
    {
        $this->expectExceptionMessage('Preset \'non-existing\' doesn\'t exist');
        $this->runCommand(app(DbRebuild::class), ['--preset' => 'non-existing'], ['Yes']);
    }

    public function testInvalidPresetName(): void
    {
        $this->expectExceptionMessage('Preset names must be strings');
        $this->runCommand(app(DbRebuild::class), ['--preset' => ['not', 'valid']], ['Yes']);
    }

    public function testInvalidBackupSetting(): void
    {
        $this->setConfig('default', [
            'backup' => '',
        ]);

        $this->expectExceptionMessage('db-rebuild.presets.default.backup should be an array');
        $this->runCommand(app(DbRebuild::class), [], ['Yes']);
    }

    public function testInvalidCommandsSetting(): void
    {
        $this->setConfig('default', [
            'commands' => '',
        ]);

        $this->expectExceptionMessage('db-rebuild.presets.default.commands should be an array');
        $this->runCommand(app(DbRebuild::class), [], ['Yes']);
    }

    public function testInvalidSeedsSetting(): void
    {
        $this->setConfig('default', [
            'seeds' => '',
        ]);

        $this->expectExceptionMessage('db-rebuild.presets.default.seeds should be an array');
        $this->runCommand(app(DbRebuild::class), [], ['Yes']);
    }

    public function testInvalidDatabaseSetting(): void
    {
        $this->setConfig('default', [
            'database' => [],
        ]);

        $this->expectExceptionMessage('db-rebuild.presets.default.database should be a string');
        $this->runCommand(app(DbRebuild::class), [], ['Yes']);
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
