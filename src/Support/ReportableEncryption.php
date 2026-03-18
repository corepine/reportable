<?php

declare(strict_types=1);

namespace Corepine\Reportable\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ReportableEncryption
{
    public static function encryptReportable(Model $reportable): array
    {
        return [
            'encryptedType' => Crypt::encryptString($reportable->getMorphClass()),
            'encryptedId' => Crypt::encryptString((string) $reportable->getKey()),
        ];
    }

    public static function decryptReportable(string $encryptedType, string $encryptedId): array
    {
        return [
            'type' => Crypt::decryptString($encryptedType),
            'id' => Crypt::decryptString($encryptedId),
        ];
    }

    public static function encryptType(string $type): string
    {
        return Crypt::encryptString($type);
    }

    public static function decryptType(string $encryptedType): string
    {
        return Crypt::decryptString($encryptedType);
    }

    public static function encryptId(mixed $id): string
    {
        return Crypt::encryptString((string) $id);
    }

    public static function decryptId(string $encryptedId): string
    {
        return Crypt::decryptString($encryptedId);
    }
}
