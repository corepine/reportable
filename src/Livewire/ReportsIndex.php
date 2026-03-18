<?php

declare(strict_types=1);

namespace Corepine\Reportable\Livewire;

use Corepine\Reportable\Models\Report;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ReportsIndex extends Component
{
    use WithPagination;

    public string $status = 'all';

    public string $search = '';

    public int $perPage = 15;

    protected $queryString = [
        'status' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = Report::query()
            ->with(['reporter', 'reportable'])
            ->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('type', 'like', "%{$this->search}%")
                    ->orWhereJsonContains('data->message', $this->search);
            });
        }

        $reports = $query->paginate($this->perPage);

        return view('corepine-reportable::livewire.reports-index', [
            'reports' => $reports,
        ]);
    }
}
