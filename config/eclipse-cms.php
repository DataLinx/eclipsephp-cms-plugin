<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-tenancy config
    |--------------------------------------------------------------------------
    */
    'tenancy' => [
        'enabled' => true,
        'model' => 'Eclipse\\Core\\Models\\Site',
        'foreign_key' => 'site_id',
    ],
];
