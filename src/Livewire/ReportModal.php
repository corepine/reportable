<?php

declare(strict_types=1);

namespace Corepine\Reportable\Livewire;

use Corepine\Reportable\Facades\Reportable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ReportModal extends Component
{
    public ?string $encryptedType = null;

    public ?string $encryptedId = null;

    public ?string $reportableType = null;

    public ?string $reportableId = null;

    public string $type = '';

    public array $data = [];

    public string $message = '';

    public bool $show = false;

    public array $typeOptions = [];

    public string $title = 'Report this content';

    public string $submitLabel = 'Send report';

    public string $cancelLabel = 'Cancel';

    protected $listeners = [
        'openReportModal' => 'open',
    ];

    public function mount(?string $encryptedType = null, ?string $encryptedId = null): void
    {
        $this->encryptedType = $encryptedType;
        $this->encryptedId = $encryptedId;

        if ($this->encryptedType && $this->encryptedId) {
            $this->decryptParams();
            $this->loadTypeOptions();
        }
    }

    public function open(?string $encryptedType = null, ?string $encryptedId = null): void
    {
        if ($encryptedType && $encryptedId) {
            $this->encryptedType = $encryptedType;
            $this->encryptedId = $encryptedId;
            $this->decryptParams();
            $this->loadTypeOptions();
        }

        $this->show = true;
        $this->reset(['type', 'message', 'data']);
    }

    public function close(): void
    {
        $this->show = false;
        $this->reset(['type', 'message', 'data', 'reportableType', 'reportableId']);
    }

    public function submit(): void
    {
        $this->validate([
            'type' => ['required', 'string'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! $this->reportableType || ! $this->reportableId) {
            throw ValidationException::withMessages([
                'reportable' => 'Invalid reportable item.',
            ]);
        }

        $reportable = $this->resolveReportable($this->reportableType, $this->reportableId);
        $reporter = auth()->user();

        if ($reporter !== null && ! $reporter instanceof Model) {
            throw ValidationException::withMessages([
                'reporter' => 'The authenticated user must be an Eloquent model to submit reports.',
            ]);
        }

        $data = array_filter([
            'message' => $this->message,
        ]);

        Reportable::for($reportable)->by($reporter)->submit($this->type, $data);

        $this->dispatch('report-submitted');
        session()->flash('reportable.status', 'Thanks, your report has been submitted.');

        $this->close();
    }

    public function render(): View
    {
        return view('corepine-reportable::livewire.report-modal');
    }

    protected function decryptParams(): void
    {
        try {
            $this->reportableType = $this->encryptedType ? Crypt::decryptString($this->encryptedType) : null;
            $this->reportableId = $this->encryptedId ? Crypt::decryptString($this->encryptedId) : null;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'encrypted' => 'Invalid encrypted parameters.',
            ]);
        }
    }

    protected function loadTypeOptions(): void
    {
        if (! $this->reportableType || ! $this->reportableId) {
            return;
        }

        try {
            $reportable = $this->resolveReportable($this->reportableType, $this->reportableId);
            $manager = app(\Corepine\Reportable\Services\ReportableManager::class);
            $this->typeOptions = $manager->typeOptionsFor($reportable);

            if (! empty($this->typeOptions)) {
                $this->type = $this->typeOptions[0]['value'] ?? '';
            }
        } catch (\Exception $e) {
            $this->typeOptions = [];
        }
    }

    protected function resolveReportable(string $type, mixed $id): Model
    {
        $class = Relation::getMorphedModel($type) ?? $type;

        if (! is_string($class) || ! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            throw ValidationException::withMessages([
                'reportable_type' => 'Invalid reportable model.',
            ]);
        }

        /** @var Model $instance */
        $instance = new $class();
        $model = $instance->newQuery()->find($id);

        if (! $model instanceof Model) {
            throw ValidationException::withMessages([
                'reportable_id' => 'The reported item could not be found.',
            ]);
        }

        return $model;
    }
}
