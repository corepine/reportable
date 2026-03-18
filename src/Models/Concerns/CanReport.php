<?php

declare(strict_types=1);

namespace Corepine\Reportable\Models\Concerns;

use BackedEnum;
use Corepine\Reportable\Enums\ReportStatus;
use Corepine\Reportable\Facades\Reportable;
use Corepine\Reportable\Models\Report;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use RuntimeException;

trait CanReport
{
    public function submittedReports(): MorphMany
    {
        /** @var class-string<Model> $reportModel */
        $reportModel = Reportable::reportModel();

        return $this->asReporterModel()->morphMany($reportModel, 'reporter');
    }

    public function reportsSubmitted(): MorphMany
    {
        return $this->submittedReports();
    }

    public function report(
        Model $reportable,
        BackedEnum|string $type,
        array $data = [],
        BackedEnum|string $status = ReportStatus::PENDING,
    ): Report {
        /** @var Report $report */
        $report = Reportable::for($reportable)->by($this->asReporterModel())->submit($type, $data, $status);

        return $report;
    }

    public function hasReported(Model $reportable): bool
    {
        return Reportable::for($reportable)->by($this->asReporterModel())->has();
    }

    public function withdrawReport(Model $reportable): int
    {
        return Reportable::for($reportable)->by($this->asReporterModel())->clear();
    }

    protected function asReporterModel(): Model
    {
        if (! $this instanceof Model) {
            throw new RuntimeException('CanReport trait must be used on an Eloquent model.');
        }

        return $this;
    }
}
