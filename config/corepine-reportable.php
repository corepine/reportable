<?php

declare(strict_types=1);

use Corepine\Reportable\Casts\ReportStatusCast;
use Corepine\Reportable\Casts\ReportTypeCast;
use Corepine\Reportable\Enums\ReportStatus;
use Corepine\Reportable\Enums\ReportType;
use Corepine\Reportable\Models\Report;

return [
    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    |
    | Set a prefix when this package shares a database with other applications.
    |
    */
    'table_prefix' => env('COREPINE_REPORTABLE_TABLE_PREFIX', 'corepine_'),

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    */
    'models' => [
        'report' => Report::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    |
    | These casts normalize report types and statuses before they are stored.
    |
    */
    'casts' => [
        'type' => ReportTypeCast::class,
        'status' => ReportStatusCast::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Enums
    |--------------------------------------------------------------------------
    |
    | Point these to your own string backed enums if you want package-wide
    | defaults with labels, descriptions, and extra metadata.
    |
    */
    'enums' => [
        'type' => ReportType::class,
        'status' => ReportStatus::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Extra Types / Statuses
    |--------------------------------------------------------------------------
    |
    | Extend or override the enum-driven defaults without replacing the enums.
    |
    */
    'types' => [
        'custom' => [
            'label' => 'Other',
            'description' => 'Share a custom reason with the moderation team.',
            'data' => [
                'requires_message' => true,
            ],
        ],
    ],

    'statuses' => [],

    /*
    |--------------------------------------------------------------------------
    | Reporting Behaviour
    |--------------------------------------------------------------------------
    */
    'allow_guest_reports' => false,

    /*
    |--------------------------------------------------------------------------
    | UI
    |--------------------------------------------------------------------------
    |
    | The starter UI is intentionally small. You can replace the routes, views,
    | middleware, or disable them entirely.
    |
    */
    'ui' => [
        'routes' => true,
        'route_prefix' => 'corepine/reportable',
        'store_path' => 'submit',
        'reports_path' => 'reports',
        'middleware' => ['web'],
        'store_middleware' => ['web', 'auth'],
        'reports_middleware' => ['web', 'auth'],
        'route_names' => [
            'store' => 'corepine-reportable.store',
            'index' => 'corepine-reportable.index',
        ],
    ],
];
