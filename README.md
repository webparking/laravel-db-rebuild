<h1 align="center">
  Laravel DB Rebuild
</h1>

<p align="center">
  <a href="https://travis-ci.org/webparking/laravel-db-rebuild">
      <img src="https://travis-ci.org/webparking/laravel-db-rebuild.svg?branch=master" alt="Build Status">
    </a> 
</p>

This package is meant to provide an easy way of resetting your development database to a certain state.

In addition to `php artisan migrate:fresh` this package also allows you to backup tables (for example sessions and admin_users, to stay logged in) and run custom seeders or commands based on presets.

This allows you to easily seed different information for local development, your tests and staging.

## Installation
Add this package to composer.

```PHP
composer require webparking/laravel-db-rebuild
```

Publish the config:

```PHP
php artisan vendor:publish --provider="Webparking\DbRebuild\ServiceProvider"
```

## Usage
The default usage is the following, this will use the default preset. It will also ask you if you're sure you want to reset the db.

```PHP
php artisan db:rebuild
```

You an skipp the question by adding the `--f` flag. You can change the preset by adding `--preset=test`

```PHP
php artisan db:rebuild --preset=test --f
```

## Config


```PHP
return [
    'presets' => [
        'default' => [
            'database' => 'local_database',
            'backup' => [
                'sessions',
                'admin_users',
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