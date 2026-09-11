<?php
namespace Tests\Legacy\Integration\Surveil\Fixture;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\AssertionFailedError;
use Tests\Legacy\Integration\BaseFixture\Server;

trait Models
{
    use Server\Laravel\Application;

    function settingValue(string $settingKey): mixed
    {
        $row = $this->query()->where('key', $settingKey)->first();
        return $row?->value ?? throw new AssertionFailedError("Failed to read missing setting: '$settingKey'");
    }

    function existingSetting(string $key, string $value): void
    {
        $this->query()->insert(['key' => $key, 'value' => $value]);
    }

    function countByKey(string $key): int
    {
        return $this->query()->where(['key' => $key])->count();
    }

    function query(): Builder
    {
        /** @var DatabaseManager $database */
        $database = $this->laravel->app->get(DatabaseManager::class);
        return $database->table('settings_key_value');
    }
}
