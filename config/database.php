<?php

return [
    'default' => 'mongodb',
    'connections' => [

         'mongodb' => [
            'driver'   => 'mongodb',
            'host'     => env('MONGO_DB_HOST', '127.0.0.1'),
            'port'     => env('MONGO_DB_PORT', 27017),
            'database' => env('MONGO_DB_DATABASE'),
            'username' => env('MONGO_DB_USERNAME'),
            'password' => env('MONGO_DB_PASSWORD'),
            'options'  => []
        ],

    ],
    'migrations' => 'migrations',
];