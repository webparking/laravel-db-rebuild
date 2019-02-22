<?php

return [
    'presets' => [
        'default' => [
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'backup' => [
                'sessions',
            ],
            'commands' => [
                'db:seed',
            ],
            'seeds' => [
            ],
        ],
    ],
];
