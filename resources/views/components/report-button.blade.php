@props([
    'reportable',
    'label' => 'Report',
    'buttonClass' => null,
    'types' => null,
])

@php
    use Illuminate\Support\Facades\Crypt;
    $encryptedType = Crypt::encryptString($reportable->getMorphClass());
    $encryptedId = Crypt::encryptString((string) $reportable->getKey());
    $defaultStyle = 'display:inline-flex;align-items:center;justify-content:center;border:1px solid #d1d5db;border-radius:999px;padding:0.55rem 0.9rem;background:#ffffff;color:#111827;font:inherit;cursor:pointer;';
@endphp

<div>
    <button
        type="button"
        class="{{ $buttonClass }}"
        @if (blank($buttonClass)) style="{{ $defaultStyle }}" @endif
        wire:click="$dispatch('openReportModal', { encryptedType: '{{ $encryptedType }}', encryptedId: '{{ $encryptedId }}' })"
    >
        {{ $label }}
    </button>
</div>
