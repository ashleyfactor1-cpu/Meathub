<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat with MeatHub — MeatHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-[#f8f5ef] px-4 py-8 text-[#292522] antialiased sm:py-12">
    <main class="mx-auto max-w-3xl">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-[#14532d] hover:underline">← Back to MeatHub</a>
        </div>

        <livewire:customer-chat />
    </main>
    @livewireScripts
</body>
</html>
