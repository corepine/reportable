<?php

declare(strict_types=1);

namespace Corepine\Reportable\Casts;

use Corepine\Reportable\Facades\Reportable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ReportTypeCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return Reportable::normalizeType($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        $type = Reportable::normalizeType($value);

        if ($type === '') {
            throw new RuntimeException("Invalid report type for [{$key}].");
        }

        return $type;
    }
}
