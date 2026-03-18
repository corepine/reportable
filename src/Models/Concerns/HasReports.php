<?php

declare(strict_types=1);

namespace Corepine\Reportable\Models\Concerns;

use BackedEnum;
use Corepine\Reportable\Enums\ReportStatus;
use Corepine\Reportable\Facades\Reportable;
use Corepine\Reportable\Models\Report;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use RuntimeException;

trait HasReports
{
    public static function bootHasReports(): void
    {
        static::deleted(function (Model $model): void {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            Reportable::for($model)->clear();
        });
    }

    public function reports(): MorphMany
    {
        /** @var class-string<Model> $reportModel */
        $reportModel = Reportable::reportModel();

        return $this->asReportableModel()->morphMany($reportModel, 'reportable');
    }

    public function pendingReports(): MorphMany
    {
        return $this->reports()->where('status', Reportable::normalizeStatus(ReportStatus::PENDING));
    }

    public function reportBy(
        BackedEnum|string $type,
        Model|Authenticatable|null $reporter = null,
        array $data = [],
        BackedEnum|string $status = ReportStatus::PENDING,
    ): Report {
        /** @var Report $report */
        $report = Reportable::for($this->asReportableModel())->by($reporter)->submit($type, $data, $status);

        return $report;
    }

    public function reportedBy(Model|Authenticatable|null $reporter = null): bool
    {
        return Reportable::for($this->asReportableModel())->by($reporter)->has();
    }

    public function reportsCount(BackedEnum|string|null $type = null, BackedEnum|string|null $status = null): int
    {
        return Reportable::for($this->asReportableModel())->count($type, $status);
    }

    public function pendingReportsCount(): int
    {
        return $this->reportsCount(status: ReportStatus::PENDING);
    }

    /**
     * @return array<int, array{value: string, label: string, description: string|null, data: array<string, mixed>}>
     */
    public function availableReportTypes(): array
    {
        return Reportable::typeOptionsFor($this->asReportableModel());
    }

    public function clearReports(): int
    {
        return Reportable::for($this->asReportableModel())->clear();
    }

    protected function asReportableModel(): Model
    {
        if (! $this instanceof Model) {
            throw new RuntimeException('HasReports trait must be used on an Eloquent model.');
        }

        return $this;
    }
}
