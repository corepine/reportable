<?php

declare(strict_types=1);

namespace Corepine\Reportable\Contracts;

interface DefinesReportOptions
{
    /**
     * @return array<int, array{value: string, label: string, description: string|null, data: array<string, mixed>}>
     */
    public static function definitions(): array;
}
