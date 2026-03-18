<?php

declare(strict_types=1);

namespace Corepine\Reportable\Casts;

use Corepine\Reportable\Facades\Reportable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ReportStatusCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return Reportable::normalizeStatus($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return Reportable::ensureSupportedStatus($value);
    }
}
