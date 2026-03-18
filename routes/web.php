<?php

declare(strict_types=1);

use Corepine\Reportable\Http\Controllers\ReportsIndexController;
use Corepine\Reportable\Http\Controllers\StoreReportController;
use Illuminate\Support\Facades\Route;

$ui = config('corepine-reportable.ui', []);
$prefix = trim((string) ($ui['route_prefix'] ?? 'corepine/reportable'), '/');
$routeNames = is_array($ui['route_names'] ?? null) ? $ui['route_names'] : [];
$baseMiddleware = is_array($ui['middleware'] ?? null) ? $ui['middleware'] : ['web'];
$storeMiddleware = is_array($ui['store_middleware'] ?? null) ? $ui['store_middleware'] : $baseMiddleware;
$reportsMiddleware = is_array($ui['reports_middleware'] ?? null) ? $ui['reports_middleware'] : $baseMiddleware;
$storePath = trim((string) ($ui['store_path'] ?? 'submit'), '/');
$reportsPath = trim((string) ($ui['reports_path'] ?? 'reports'), '/');

Route::prefix($prefix)->group(function () use ($reportsMiddleware, $reportsPath, $routeNames, $storeMiddleware, $storePath): void {
    Route::middleware($storeMiddleware)
        ->post($storePath, StoreReportController::class)
        ->name($routeNames['store'] ?? 'corepine-reportable.store');

    Route::middleware($reportsMiddleware)
        ->get($reportsPath, ReportsIndexController::class)
        ->name($routeNames['index'] ?? 'corepine-reportable.index');
});
