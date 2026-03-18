<?php

declare(strict_types=1);

namespace Corepine\Reportable\Services;

use BackedEnum;
use Corepine\Reportable\Enums\ReportStatus;
use Corepine\Reportable\Facades\Reportable;
use Corepine\Reportable\Models\Report;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReportService
{
    protected ?Model $reportable = null;

    protected ?Model $reporter = null;

    public function for(Model $reportable): static
    {
        $this->reportable = $reportable;

        return $this;
    }

    public function on(Model $reportable): static
    {
        return $this->for($reportable);
    }

    public function by(Model|Authenticatable|null $reporter = null): static
    {
        if ($reporter === null) {
            $reporter = auth()->user();
        }

        $this->reporter = $reporter instanceof Model ? $reporter : null;

        return $this;
    }

    public function actingAs(Model|Authenticatable|null $reporter = null): static
    {
        return $this->by($reporter);
    }

    public function submit(BackedEnum|string $type, array $data = [], BackedEnum|string $status = ReportStatus::PENDING): Report
    {
        $this->guardTargetContext();

        $resolvedType = Reportable::ensureSupportedType($type, $this->reportable);
        $resolvedStatus = Reportable::ensureSupportedStatus($status);
        $payload = $this->normalizeData($data);
        $reportModel = Reportable::reportModel();

        if ($this->reporter === null && ! Reportable::allowGuestReports()) {
            throw new RuntimeException('Guest reports are disabled.');
        }

        return DB::transaction(function () use ($payload, $reportModel, $resolvedStatus, $resolvedType): Report {
            $attributes = [
                'reportable_type' => $this->reportable->getMorphClass(),
                'reportable_id' => $this->reportable->getKey(),
            ];

            if ($this->reporter !== null) {
                $attributes['reporter_type'] = $this->reporter->getMorphClass();
                $attributes['reporter_id'] = $this->reporter->getKey();

                /** @var Report $report */
                $report = $reportModel::query()->updateOrCreate($attributes, [
                    'type' => $resolvedType,
                    'status' => $resolvedStatus,
                    'data' => $payload,
                ]);

                return $report->refresh();
            }

            /** @var Report $report */
            $report = $reportModel::query()->create(array_merge($attributes, [
                'type' => $resolvedType,
                'status' => $resolvedStatus,
                'data' => $payload,
            ]));

            return $report->refresh();
        });
    }

    public function existing(): ?Report
    {
        $this->guardTargetContext();

        if ($this->reporter === null) {
            return null;
        }

        /** @var Report|null $report */
        $report = (clone $this->baseReporterQuery())->first();

        return $report;
    }

    public function has(): bool
    {
        return $this->existing() !== null;
    }

    public function clear(): int
    {
        $query = $this->baseTargetQuery();

        if ($this->reporter !== null) {
            $query->forReporter($this->reporter);
        }

        return (clone $query)->delete();
    }

    public function count(BackedEnum|string|null $type = null, BackedEnum|string|null $status = null): int
    {
        $query = $this->baseTargetQuery();

        if ($type !== null) {
            $query->where('type', Reportable::ensureSupportedType($type, $this->reportable));
        }

        if ($status !== null) {
            $query->where('status', Reportable::ensureSupportedStatus($status));
        }

        return (int) $query->count();
    }

    public function resolve(Report|int $report, BackedEnum|string $status = ReportStatus::RESOLVED, array $data = []): Report
    {
        $resolvedStatus = Reportable::ensureSupportedStatus($status);
        $model = $report instanceof Report ? $report : $this->findReportById($report);

        $existingData = is_array($model->data) ? $model->data : [];

        $model->forceFill([
            'status' => $resolvedStatus,
            'data' => array_replace($existingData, $this->normalizeData($data)),
        ]);

        $model->save();

        return $model->refresh();
    }

    protected function baseTargetQuery(): Builder
    {
        $this->guardTargetContext();

        $reportModel = Reportable::reportModel();

        return $reportModel::query()->forReportable($this->reportable);
    }

    protected function baseReporterQuery(): Builder
    {
        $this->guardTargetContext();
        $this->guardReporterContext();

        return $this->baseTargetQuery()->forReporter($this->reporter);
    }

    protected function findReportById(int $id): Report
    {
        $reportModel = Reportable::reportModel();

        /** @var Report $report */
        $report = $reportModel::query()->findOrFail($id);

        return $report;
    }

    protected function guardTargetContext(): void
    {
        if (! $this->reportable instanceof Model) {
            throw new RuntimeException('A reportable model must be set before performing reporting operations.');
        }
    }

    protected function guardReporterContext(): void
    {
        if (! $this->reporter instanceof Model) {
            throw new RuntimeException('A reporter model must be set before performing reporter scoped operations.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeData(array $data): array
    {
        return collect($data)
            ->filter(static fn (mixed $value): bool => $value !== null && $value !== '')
            ->all();
    }
}
