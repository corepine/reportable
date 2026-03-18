<?php

declare(strict_types=1);

namespace Corepine\Reportable\Models;

use BackedEnum;
use Corepine\Reportable\Enums\ReportStatus;
use Corepine\Reportable\Facades\Reportable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    protected $fillable = [
        'reporter_id',
        'reporter_type',
        'reportable_id',
        'reportable_type',
        'type',
        'status',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        if ($this->table === null) {
            $this->table = Reportable::formatTableName('reports');
        }

        parent::__construct($attributes);

        $this->mergeCasts([
            'type' => Reportable::reportTypeCast(),
            'status' => Reportable::reportStatusCast(),
        ]);
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo(null, 'reportable_type', 'reportable_id', 'id');
    }

    public function reporter(): MorphTo
    {
        return $this->morphTo('reporter', 'reporter_type', 'reporter_id', 'id');
    }

    public function scopeForReporter(Builder $query, Model $reporter): Builder
    {
        return $query
            ->where('reporter_id', $reporter->getKey())
            ->where('reporter_type', $reporter->getMorphClass());
    }

    public function scopeForReportable(Builder $query, Model $reportable): Builder
    {
        return $query
            ->where('reportable_id', $reportable->getKey())
            ->where('reportable_type', $reportable->getMorphClass());
    }

    public function scopeWhereType(Builder $query, BackedEnum|string $type): Builder
    {
        return $query->where('type', Reportable::normalizeType($type));
    }

    public function scopeWhereStatus(Builder $query, BackedEnum|string $status): Builder
    {
        return $query->where('status', Reportable::normalizeStatus($status));
    }

    public function isPending(): bool
    {
        return (string) $this->getAttribute('status') === Reportable::normalizeStatus(ReportStatus::PENDING);
    }
}
