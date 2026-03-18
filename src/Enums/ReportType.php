<?php

declare(strict_types=1);

namespace Corepine\Reportable\Enums;

use Corepine\Reportable\Contracts\DefinesReportOptions;

enum ReportType: string implements DefinesReportOptions
{
    case SPAM = 'spam';
    case OBSCENE = 'obscene';
    case HARASSMENT = 'harassment';
    case MISLEADING = 'misleading';
    case VIOLENCE = 'violence';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::SPAM => 'Spam',
            self::OBSCENE => 'Obscene Content',
            self::HARASSMENT => 'Harassment',
            self::MISLEADING => 'Misleading',
            self::VIOLENCE => 'Violence',
            self::CUSTOM => 'Other',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SPAM => 'Promotional, repetitive, or irrelevant content.',
            self::OBSCENE => 'Sexual, obscene, or not safe for the audience.',
            self::HARASSMENT => 'Bullying, threats, hate, or targeted abuse.',
            self::MISLEADING => 'False, deceptive, or manipulative content.',
            self::VIOLENCE => 'Graphic violence or glorified physical harm.',
            self::CUSTOM => 'Share another reason with the moderation team.',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return match ($this) {
            self::CUSTOM => ['requires_message' => true],
            default => [],
        };
    }

    public static function definitions(): array
    {
        return array_map(
            static fn (self $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
                'description' => $type->description(),
                'data' => $type->data(),
            ],
            self::cases(),
        );
    }
}
