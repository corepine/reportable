@props([
    'title' => 'Report this content',
    'submitLabel' => 'Send report',
    'cancelLabel' => 'Cancel',
])

@php
    use Illuminate\Support\Facades\Crypt;
@endphp

@livewire('corepine-report-modal', [
    'title' => $title,
    'submitLabel' => $submitLabel,
    'cancelLabel' => $cancelLabel,
])
