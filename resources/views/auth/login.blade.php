<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — MeatHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8f5ef] px-4 py-10 text-[#292522] antialiased sm:py-16">
    <main class="mx-auto max-w-md">
        <a href="{{ route('home') }}" class="text-sm font-semibold text-[#14532d] hover:underline">← Back to MeatHub</a>
        <section class="mt-5 rounded-3xl border border-[#ece3d9] bg-[#fffdfa] p-6 shadow-sm sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[.14em] text-[#4f7657]">Customer account</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1f2937]">Welcome back</h1>
            <p class="mt-2 text-sm text-[#716761]">Sign in to view your orders and status notifications.</p>
            <form action="{{ route('account.login.store') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-[#514740]">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 h-12 w-full rounded-xl border border-[#e8dfd5] bg-white px-4 text-sm outline-none focus:border-[#16a34a] focus:ring-2 focus:ring-[#16a34a]/10">
                    @error('email')
                        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-[#514740]">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-2 h-12 w-full rounded-xl border border-[#e8dfd5] bg-white px-4 text-sm outline-none focus:border-[#16a34a] focus:ring-2 focus:ring-[#16a34a]/10">
                </div>
                <label class="flex items-center gap-2 text-sm text-[#655c56]">
                    <input type="checkbox" name="remember" value="1" class="rounded border-[#d8cec4] text-[#14532d] focus:ring-[#16a34a]">
                    Remember me
                </label>
                <button type="submit" class="w-full rounded-xl bg-[#14532d] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#166534]">Sign in</button>
            </form>
            <p class="mt-5 text-center text-sm text-[#716761]">New to MeatHub? <a href="{{ route('account.register') }}" class="font-semibold text-[#14532d] hover:underline">Create an account</a></p>
        </section>
    </main>
</body>
</html>
