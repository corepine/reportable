{{-- 
    Example Usage in a Blade View
--}}

<div>
    <h1>My Post</h1>
    <p>{{ $post->content }}</p>
    
    {{-- Simple button with encrypted parameters --}}
    <x-corepine-report-button :reportable="$post" label="Report Post" />
    
    {{-- Custom styled button --}}
    <x-corepine-report-button 
        :reportable="$post" 
        label="Flag Content"
        button-class="bg-red-500 text-white px-4 py-2 rounded"
    />
</div>

{{-- Manual usage with wire:click --}}
@php
    use Illuminate\Support\Facades\Crypt;
    $encryptedType = Crypt::encryptString($post->getMorphClass());
    $encryptedId = Crypt::encryptString((string) $post->getKey());
@endphp

<button 
    type="button"
    wire:click="$dispatch('openReportModal', { 
        encryptedType: '{{ $encryptedType }}', 
        encryptedId: '{{ $encryptedId }}' 
    })"
    class="report-button"
>
    Report This Content
</button>

{{-- Using with JavaScript --}}
<button 
    type="button"
    onclick="Livewire.dispatch('openReportModal', { 
        encryptedType: '{{ $encryptedType }}', 
        encryptedId: '{{ $encryptedId }}' 
    })"
>
    Report with JavaScript
</button>
