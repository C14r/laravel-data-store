<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Namespace
    |--------------------------------------------------------------------------
    |
    | This option controls the default namespace used when no namespace is
    | explicitly specified. Set to null for no default namespace.
    |
    */

    'default_namespace' => null,

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | The database table name used to store data. You can customize this
    | if you need a different table name.
    |
    */

    'table_name' => 'data_stores',

    /*
    |--------------------------------------------------------------------------
    | Default TTL
    |--------------------------------------------------------------------------
    |
    | Default time-to-live in seconds for entries. Set to null for no
    | expiration by default. Can be overridden per entry.
    |
    */

    'default_ttl' => null,

    /*
    |--------------------------------------------------------------------------
    | Auto Cleanup
    |--------------------------------------------------------------------------
    |
    | Automatically schedule cleanup of expired entries. If enabled, the
    | cleanup command will run daily via Laravel's scheduler.
    |
    */

    'auto_cleanup' => true,

    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for export/import operations.
    |
    */

    'export' => [
        'disk' => env('DATASTORE_EXPORT_DISK', 'local'),
        'path' => env('DATASTORE_EXPORT_PATH', 'datastore-exports'),
    ],

];
