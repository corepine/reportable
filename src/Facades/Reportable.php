<?php

declare(strict_types=1);

namespace Corepine\Reportable\Facades;

use Illuminate\Support\Facades\Facade;

class Reportable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Corepine\Reportable\Services\ReportableManager::class;
    }
}
