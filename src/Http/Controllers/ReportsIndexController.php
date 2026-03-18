<?php

declare(strict_types=1);

namespace Corepine\Reportable\Http\Controllers;

use Corepine\Reportable\Facades\Reportable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportsIndexController
{
    public function __invoke(Request $request): View
    {
        $reportModel = Reportable::reportModel();
        $types = collect(Reportable::typeOptionsFor())->keyBy('value');
        $statuses = collect(Reportable::statusOptions())->keyBy('value');

        $reports = $reportModel::query()
            ->with(['reporter', 'reportable'])
            ->when($request->filled('type'), function ($query) use ($request, $types): void {
                $type = Str::lower(trim((string) $request->string('type')));

                if ($types->has($type)) {
                    $query->where('type', $type);
                }
            })
            ->when($request->filled('status'), function ($query) use ($request, $statuses): void {
                $status = Str::lower(trim((string) $request->string('status')));

                if ($statuses->has($status)) {
                    $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('corepine-reportable::pages.reports-index', [
            'reports' => $reports,
            'types' => $types,
            'statuses' => $statuses,
            'filters' => Collection::make([
                'type' => (string) $request->string('type'),
                'status' => (string) $request->string('status'),
            ]),
        ]);
    }
}
