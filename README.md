# laravel-db-rebuild

This package is meant to provide an easy way of resetting your development database to a certain state.

In addition to `php artisan migrate:reset` this package also allows you to backup tables (for example sessions and admin_users, to stay logged in) and run custom seeders or commands based on presets.

This allows you to easily seed different information for local development, your tests and staging.

## Installation
// Todo

## Usage
The default usage is the following, this will use the default preset. It will also ask you if you're sure you want to reset the db.

`php artisan db:rebuild`

You an skipp the question by adding the `--f` flag. You can change the preset by adding `--preset=test`

`php artisan db:rebuild --preset=test --f`

## Config
```PHP
return [
    'presets' => [
        'default' => [
            'database' => 'local_database',
            'backup' => [
                'sessions',
            ],
            'commands' => [
                'db:seed',
            ],
            'seeds' => [
            ],
        ],
        'test' => [
            'database' => 'testing_database',
            'backup' => [
                'sessions',
            ],
            'commands' => [
                'db:seed',
            ],
            'seeds' => [
                TestSeeder::class
            ],
        ],
    ],
];
```