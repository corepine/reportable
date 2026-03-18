{{-- 
    Example Layout File
    Place this in your resources/views/layouts/app.blade.php
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    
    {{-- Livewire Styles --}}
    @livewireStyles
    
    {{-- Alpine.js for modal interactions --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    {{ $slot }}
    
    {{-- Include the Livewire Report Modal Component Once --}}
    <x-corepine-report-modal />
    
    {{-- Livewire Scripts --}}
    @livewireScripts
</body>
</html>
