<?php

namespace Webparking\DbRebuild;

use RuntimeException;

class Config
{
    /** @var string */
    private $preset;

    public function __construct($preset)
    {
        if (!\is_string($preset)) {
            throw new RuntimeException('Preset names must be strings');
        }

        if (config('db-rebuild.presets.' . $preset) === null) {
            throw new RuntimeException("Preset '{$preset}' doesn't exist");
        }

        $this->preset = $preset;
    }

    private function getConfig(string $key, $default)
    {
        return config('db-rebuild.presets.' . $this->preset . '.' . $key, $default);
    }

    private function getArrayConfig(string $key, array $default = []): array
    {
        $data = $this->getConfig($key, $default);

        if (\is_array($data)) {
            return $data;
        }

        throw new RuntimeException("db-rebuild.presets.{$this->preset}.{$key} should be an array");
    }

    private function getStringConfig(string $key, string $default): string
    {
        $data = $this->getConfig($key, $default);

        if (\is_string($data)) {
            return $data;
        }

        throw new RuntimeException("db-rebuild.presets.{$this->preset}.{$key} should be a string");
    }

    public function getDatabase(): string
    {
        return $this->getStringConfig('database', config('database.connections.' . config('database.default') . '.database'));
    }

    public function getCommands(): array
    {
        return $this->getArrayConfig('commands');
    }

    public function getBackup(): array
    {
        return $this->getArrayConfig('backup');
    }

    public function getSeeds(): array
    {
        return $this->getArrayConfig('seeds');
    }
}
