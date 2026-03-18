<?php

declare(strict_types=1);

namespace Corepine\Reportable\Services;

use BackedEnum;
use Corepine\Reportable\Casts\ReportStatusCast;
use Corepine\Reportable\Casts\ReportTypeCast;
use Corepine\Reportable\Contracts\DefinesReportOptions;
use Corepine\Reportable\Models\Report;
use Corepine\Reportable\Support\ReportOption;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class ReportableManager
{
    public function app(): self
    {
        return $this;
    }

    public function tablePrefix(): string
    {
        return (string) config('corepine-reportable.table_prefix', '');
    }

    public function formatTableName(string $table): string
    {
        return $this->tablePrefix() . $table;
    }

    /**
     * @return class-string<Model>
     */
    public function reportModel(): string
    {
        $model = config('corepine-reportable.models.report', Report::class);

        if (! is_string($model) || ! is_subclass_of($model, Model::class)) {
            throw new RuntimeException('corepine-reportable.models.report must be an Eloquent model class.');
        }

        return $model;
    }

    public function newReportModel(): Model
    {
        $model = $this->reportModel();

        return new $model();
    }

    /**
     * @return class-string<CastsAttributes>
     */
    public function reportTypeCast(): string
    {
        return $this->castClass('type', ReportTypeCast::class);
    }

    /**
     * @return class-string<CastsAttributes>
     */
    public function reportStatusCast(): string
    {
        return $this->castClass('status', ReportStatusCast::class);
    }

    /**
     * @return class-string<BackedEnum>|null
     */
    public function reportTypeEnum(): ?string
    {
        return $this->enumClass('type');
    }

    /**
     * @return class-string<BackedEnum>|null
     */
    public function reportStatusEnum(): ?string
    {
        return $this->enumClass('status');
    }

    /**
     * @return array<string, ReportOption>
     */
    public function typeDefinitionsFor(?Model $reportable = null): array
    {
        return $this->mergeOptionSets(
            $this->optionsFromConfiguredEnum($this->reportTypeEnum()),
            $this->optionsFromDefinitions(config('corepine-reportable.types', [])),
            $this->reportableTypeDefinitions($reportable),
        );
    }

    /**
     * @return array<string, ReportOption>
     */
    public function statusDefinitions(): array
    {
        return $this->mergeOptionSets(
            $this->optionsFromConfiguredEnum($this->reportStatusEnum()),
            $this->optionsFromDefinitions(config('corepine-reportable.statuses', [])),
        );
    }

    /**
     * @return array<int, array{value: string, label: string, description: string|null, data: array<string, mixed>}>
     */
    public function typeOptionsFor(?Model $reportable = null): array
    {
        return array_map(
            static fn (ReportOption $option): array => $option->toArray(),
            array_values($this->typeDefinitionsFor($reportable)),
        );
    }

    /**
     * @return array<int, array{value: string, label: string, description: string|null, data: array<string, mixed>}>
     */
    public function statusOptions(): array
    {
        return array_map(
            static fn (ReportOption $option): array => $option->toArray(),
            array_values($this->statusDefinitions()),
        );
    }

    public function normalizeType(BackedEnum|string $type): string
    {
        return $this->normalizeValue($type, 'Report type');
    }

    public function normalizeStatus(BackedEnum|string $status): string
    {
        return $this->normalizeValue($status, 'Report status');
    }

    public function ensureSupportedType(BackedEnum|string $type, ?Model $reportable = null): string
    {
        $resolved = $this->normalizeType($type);

        if (! array_key_exists($resolved, $this->typeDefinitionsFor($reportable))) {
            throw new RuntimeException("Unsupported report type [{$resolved}].");
        }

        return $resolved;
    }

    public function ensureSupportedStatus(BackedEnum|string $status): string
    {
        $resolved = $this->normalizeStatus($status);

        if (! array_key_exists($resolved, $this->statusDefinitions())) {
            throw new RuntimeException("Unsupported report status [{$resolved}].");
        }

        return $resolved;
    }

    public function allowGuestReports(): bool
    {
        return (bool) config('corepine-reportable.allow_guest_reports', false);
    }

    public function builder(): ReportService
    {
        return new ReportService();
    }

    public function for(Model $reportable): ReportService
    {
        return $this->builder()->for($reportable);
    }

    /**
     * @return class-string<CastsAttributes>
     */
    protected function castClass(string $key, string $default): string
    {
        $cast = config("corepine-reportable.casts.{$key}", $default);

        if (! is_string($cast) || trim($cast) === '') {
            $cast = $default;
        }

        if (! class_exists($cast) || ! is_subclass_of($cast, CastsAttributes::class)) {
            throw new RuntimeException("corepine-reportable.casts.{$key} must be a valid Eloquent cast class.");
        }

        return $cast;
    }

    /**
     * @return class-string<BackedEnum>|null
     */
    protected function enumClass(string $key): ?string
    {
        $enum = config("corepine-reportable.enums.{$key}");

        if (! is_string($enum) || trim($enum) === '') {
            return null;
        }

        if (! class_exists($enum) || ! is_subclass_of($enum, BackedEnum::class)) {
            throw new RuntimeException("corepine-reportable.enums.{$key} must be a string backed enum class.");
        }

        return $enum;
    }

    /**
     * @param  class-string<BackedEnum>|null  $enumClass
     * @return array<string, ReportOption>
     */
    protected function optionsFromConfiguredEnum(?string $enumClass): array
    {
        if ($enumClass === null) {
            return [];
        }

        if (is_subclass_of($enumClass, DefinesReportOptions::class)) {
            return $this->optionsFromDefinitions($enumClass::definitions());
        }

        $options = [];

        foreach ($enumClass::cases() as $case) {
            $option = ReportOption::fromEnumCase($case);
            $options[$option->value] = $option;
        }

        return $options;
    }

    /**
     * @return array<string, ReportOption>
     */
    protected function optionsFromDefinitions(mixed $definitions): array
    {
        if ($definitions === null) {
            return [];
        }

        if ($definitions instanceof Collection) {
            $definitions = $definitions->all();
        }

        if ($definitions instanceof BackedEnum) {
            $option = ReportOption::fromEnumCase($definitions);

            return [$option->value => $option];
        }

        if (is_string($definitions) && class_exists($definitions) && is_subclass_of($definitions, BackedEnum::class)) {
            return $this->optionsFromConfiguredEnum($definitions);
        }

        if (! is_array($definitions)) {
            return [];
        }

        $options = [];

        foreach ($definitions as $key => $definition) {
            if ($definition instanceof BackedEnum) {
                $option = ReportOption::fromEnumCase($definition);
                $options[$option->value] = $option;

                continue;
            }

            if (is_array($definition) && isset($definition['value']) && is_string($definition['value'])) {
                $option = ReportOption::fromDefinition($definition['value'], $definition);
                $options[$option->value] = $option;

                continue;
            }

            if (is_int($key) && is_string($definition)) {
                $option = ReportOption::fromDefinition($definition, $definition);
                $options[$option->value] = $option;

                continue;
            }

            if (is_string($key)) {
                $option = ReportOption::fromDefinition($key, $definition);
                $options[$option->value] = $option;
            }
        }

        return $options;
    }

    /**
     * @return array<string, ReportOption>
     */
    protected function reportableTypeDefinitions(?Model $reportable): array
    {
        if ($reportable === null) {
            return [];
        }

        foreach (['reportTypes', 'reportableTypes'] as $method) {
            if (method_exists($reportable, $method)) {
                return $this->optionsFromDefinitions($reportable->{$method}());
            }
        }

        return [];
    }

    /**
     * @param  array<string, ReportOption>  ...$sets
     * @return array<string, ReportOption>
     */
    protected function mergeOptionSets(array ...$sets): array
    {
        $merged = [];

        foreach ($sets as $set) {
            foreach ($set as $value => $option) {
                $merged[$value] = $option;
            }
        }

        return $merged;
    }

    protected function normalizeValue(BackedEnum|string $value, string $label): string
    {
        if ($value instanceof BackedEnum) {
            if (! is_string($value->value)) {
                throw new RuntimeException("{$label} enums must use string backed values.");
            }

            $value = $value->value;
        }

        $normalized = Str::lower(trim($value));

        if ($normalized === '') {
            throw new RuntimeException("{$label} cannot be empty.");
        }

        return $normalized;
    }
}
