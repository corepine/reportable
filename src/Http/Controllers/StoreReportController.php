<?php

declare(strict_types=1);

namespace Corepine\Reportable\Http\Controllers;

use Corepine\Reportable\Facades\Reportable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StoreReportController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reportable_type' => ['required', 'string'],
            'reportable_id' => ['required'],
            'type' => ['required', 'string'],
            'data' => ['nullable', 'array'],
        ]);

        $reportable = $this->resolveReportable(
            $validated['reportable_type'],
            $validated['reportable_id'],
        );

        $reporter = $request->user();

        abort_if(
            $reporter !== null && ! $reporter instanceof Model,
            403,
            'The authenticated user must be an Eloquent model to submit reports.'
        );

        Reportable::for($reportable)->by($reporter)->submit(
            $validated['type'],
            $validated['data'] ?? [],
        );

        return back()->with('reportable.status', 'Thanks, your report has been submitted.');
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
