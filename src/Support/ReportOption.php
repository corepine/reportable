<?php

declare(strict_types=1);

namespace Corepine\Reportable\Support;

use BackedEnum;
use Illuminate\Support\Str;
use RuntimeException;

final class ReportOption
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly string $value,
        public readonly string $label,
        public readonly ?string $description = null,
        public readonly array $data = [],
    ) {}

    public static function fromEnumCase(BackedEnum $case): self
    {
        if (! is_string($case->value)) {
            throw new RuntimeException('Report option enums must use string backed values.');
        }

        $label = method_exists($case, 'label') ? $case->label() : Str::headline($case->value);
        $description = method_exists($case, 'description') ? $case->description() : null;
        $data = method_exists($case, 'data') ? $case->data() : [];

        return new self(
            self::normalizeValue($case->value),
            is_string($label) && trim($label) !== '' ? trim($label) : Str::headline($case->value),
            is_string($description) && trim($description) !== '' ? trim($description) : null,
            is_array($data) ? $data : [],
        );
    }

    public static function fromDefinition(string $value, mixed $definition): self
    {
        $normalizedValue = self::normalizeValue($value);

        if ($definition instanceof self) {
            return new self(
                $normalizedValue,
                $definition->label,
                $definition->description,
                $definition->data,
            );
        }

        if ($definition instanceof BackedEnum) {
            return self::fromEnumCase($definition);
        }

        if (is_string($definition)) {
            $label = trim($definition) !== '' ? trim($definition) : Str::headline($normalizedValue);

            return new self($normalizedValue, $label);
        }

        if (is_array($definition)) {
            $label = $definition['label'] ?? Str::headline($normalizedValue);
            $description = $definition['description'] ?? null;
            $data = $definition['data'] ?? [];

            return new self(
                $normalizedValue,
                is_string($label) && trim($label) !== '' ? trim($label) : Str::headline($normalizedValue),
                is_string($description) && trim($description) !== '' ? trim($description) : null,
                is_array($data) ? $data : [],
            );
        }

        return new self($normalizedValue, Str::headline($normalizedValue));
    }

    /**
     * @return array{value: string, label: string, description: string|null, data: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'description' => $this->description,
            'data' => $this->data,
        ];
    }

    private static function normalizeValue(string $value): string
    {
        $normalized = Str::lower(trim($value));

        if ($normalized === '') {
            throw new RuntimeException('Report option values cannot be empty.');
        }

        return $normalized;
    }
}
