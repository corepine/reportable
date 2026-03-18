@props([
    'reportable',
    'type' => null,
    'status' => null,
    'label' => null,
])

@php
    $count = $reportable->reportsCount($type, $status);
@endphp

<span
    style="display:inline-flex;align-items:center;gap:0.35rem;border-radius:999px;background:#f3f4f6;color:#111827;padding:0.35rem 0.65rem;font-size:0.875rem;"
>
    {{ $label ?? ($count . ' reports') }}
</span>
