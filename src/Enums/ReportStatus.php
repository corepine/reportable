<?php

declare(strict_types=1);

namespace Corepine\Reportable\Enums;

use Corepine\Reportable\Contracts\DefinesReportOptions;

enum ReportStatus: string implements DefinesReportOptions
{
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case RESOLVED = 'resolved';
    case DISMISSED = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::UNDER_REVIEW => 'Under Review',
            self::RESOLVED => 'Resolved',
            self::DISMISSED => 'Dismissed',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'Waiting for moderator review.',
            self::UNDER_REVIEW => 'Actively being reviewed.',
            self::RESOLVED => 'Moderation action has been completed.',
            self::DISMISSED => 'The report was reviewed and dismissed.',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return [];
    }

    public static function definitions(): array
    {
        return array_map(
            static fn (self $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
                'description' => $status->description(),
                'data' => $status->data(),
            ],
            self::cases(),
        );
    }
}
