<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#14532d">
    <title>MeatHub — Local meat, thoughtfully sourced</title>
    <meta name="description" content="Shop pork, beef, chicken and organ meat from local MeatHub producers.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8f5ef] pb-24 text-[#292522] antialiased">
    <div class="bg-[#14532d] px-4 py-2 text-center text-xs font-medium tracking-wide text-white sm:text-sm">
        GOOD MEAT, FROM PEOPLE WHO CARE <span class="mx-2 text-[#e2bd91]">•</span> Meet your local producers
    </div>

    <header class="sticky top-0 z-20 border-b border-[#e9e1d7] bg-[#fbf9f5]/95 backdrop-blur">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-4 py-4 sm:px-6 lg:px-8" aria-label="Main navigation">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="MeatHub home">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#14532d] text-xl text-white" aria-hidden="true">M</span>
                <span class="text-xl font-bold tracking-tight text-[#14532d]">meathub<span class="text-[#a16b46]">.</span></span>
            </a>

            <div class="hidden items-center gap-8 text-sm font-medium text-[#655c56] md:flex">
                <a href="#market" class="transition hover:text-[#14532d]">Shop the market</a>
                <a href="#why-meathub" class="transition hover:text-[#14532d]">Our producers</a>
            </div>

            <a href="#market" class="inline-flex items-center gap-2 rounded-full bg-[#14532d] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#166534] sm:px-5">
                Explore products <span aria-hidden="true">↗</span>
            </a>
        </nav>
    </header>

    <main>
        <section class="mx-auto grid max-w-7xl gap-10 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1.05fr_.95fr] lg:items-center lg:px-8 lg:py-20">
            <div class="max-w-2xl">
                <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-[#eadbd2] bg-white px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[.13em] text-[#166534]">
                    <span class="h-2 w-2 rounded-full bg-[#bd7656]"></span> A better kind of meat market
                </p>
                <h1 class="text-4xl font-semibold leading-[1.08] tracking-[-.045em] text-[#1f2937] sm:text-5xl lg:text-6xl">
                    Know where your <span class="text-[#15803d]">good food</span> comes from.
                </h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-[#716761] sm:text-lg sm:leading-8">
                    Shop thoughtfully raised meat from local producers. Find a favorite cut, learn who made it possible, and bring something good to the table.
                </p>
                <form action="{{ route('home') }}" method="GET" class="mt-8 flex max-w-xl items-center gap-2 rounded-2xl border border-[#e6ddd2] bg-white p-2 shadow-[0_12px_35px_-28px_rgba(74,38,35,.45)] focus-within:border-[#a75a53]">
                    <label class="sr-only" for="hero-search">Search products or producers</label>
                    <span class="pl-3 text-xl text-[#8b8178]" aria-hidden="true">⌕</span>
                    <input id="hero-search" type="search" name="q" value="{{ $search }}" placeholder="Try “pork belly” or a producer name" class="h-11 min-w-0 flex-1 border-0 bg-transparent text-sm outline-none placeholder:text-[#a39a91] focus:ring-0">
                    <button type="submit" class="h-11 shrink-0 rounded-xl bg-[#14532d] px-4 text-sm font-semibold text-white transition hover:bg-[#166534] sm:px-5">Search</button>
                </form>
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-[#81766e]">
                    <span class="mr-1">Popular:</span>
                    @foreach (['Pork', 'Beef', 'Chicken'] as $popularCategory)
                        <a href="{{ route('home', ['category' => $popularCategory]) }}#market" class="rounded-full border border-[#e8dfd5] bg-white px-3 py-1.5 font-medium transition hover:border-[#c89a8c] hover:text-[#14532d]">{{ $popularCategory }}</a>
                    @endforeach
                </div>

                <div id="why-meathub" class="mt-8 flex flex-wrap gap-x-7 gap-y-3 border-t border-[#e8dfd5] pt-6 text-sm text-[#655c56]">
                    <span class="flex items-center gap-2"><span class="text-[#15803d]">✓</span> Local producers</span>
                    <span class="flex items-center gap-2"><span class="text-[#15803d]">✓</span> Clear per-kg pricing</span>
                    <span class="flex items-center gap-2"><span class="text-[#15803d]">✓</span> Easy to shop</span>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-xl">
                <div class="absolute -left-4 top-8 h-24 w-24 rounded-full bg-[#e6c8a9] opacity-60 blur-2xl sm:-left-7"></div>
                <div class="relative overflow-hidden rounded-[2rem] bg-[#14532d] p-5 shadow-[0_24px_70px_-30px_rgba(20,83,45,.5)] sm:p-7">
                    <div class="absolute -right-14 -top-20 h-64 w-64 rounded-full border-[34px] border-white/5"></div>
                    <div class="relative flex items-center justify-between text-xs font-semibold uppercase tracking-[.16em] text-white/70">
                        <span>Good food, closer to home</span>
                        <span class="rounded-full bg-white/10 px-3 py-1.5 normal-case tracking-normal text-white">Local selection</span>
                    </div>
                    <div class="relative mt-5 overflow-hidden rounded-2xl bg-[#e9dfd0]">
                        <img
                            src="https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?auto=format&amp;fit=crop&amp;w=1100&amp;q=85"
                            alt="Freshly prepared cuts of meat"
                            class="h-64 w-full object-cover sm:h-80"
                            fetchpriority="high">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#101d17]/65 via-transparent to-transparent"></div>
                        <div class="absolute bottom-5 left-5 right-5 flex items-end justify-between gap-4 text-white">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[.14em] text-white/75">A better market run</p>
                                <p class="mt-1 text-2xl font-semibold tracking-tight">Meet your next favorite.</p>
                            </div>
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-xl text-[#14532d]" aria-hidden="true">↗</span>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-5 right-3 rounded-xl border border-[#ede9df] bg-white px-4 py-3 shadow-lg sm:right-8">
                    <p class="text-xs text-[#82766e]">From local hands</p>
                    <p class="mt-0.5 text-sm font-semibold text-[#14532d]">Made for your table <span class="text-[#a16b46]">✳</span></p>
                </div>
            </div>
        </section>

        <section id="market" class="scroll-mt-24 border-t border-[#ece3d9] bg-[#fffdfa] py-12 sm:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.16em] text-[#4f7657]">The local market</p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-[#1f2937] sm:text-4xl">Shop by category</h2>
                        <p class="mt-2 text-sm text-[#82766e]">Good cuts, from producers close to home.</p>
                    </div>
                    <span class="text-sm text-[#82766e]">{{ $products->total() }} {{ Str::plural('product', $products->total()) }} available</span>
                </div>

                <form action="{{ route('home') }}" method="GET" class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <label class="relative block">
                        <span class="sr-only">Search products or producers</span>
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-lg text-[#958a80]" aria-hidden="true">⌕</span>
                        <input
                            type="search"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Search products or producers..."
                            class="h-12 w-full rounded-xl border border-[#e8dfd5] bg-white pl-11 pr-4 text-sm outline-none transition placeholder:text-[#a39a91] focus:border-[#16a34a] focus:ring-2 focus:ring-[#16a34a]/10 sm:w-80">
                    </label>
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                    <div class="flex items-center gap-2">
                        <label class="sr-only" for="sort">Sort products</label>
                        <select id="sort" name="sort" class="h-12 min-w-0 flex-1 rounded-xl border border-[#e8dfd5] bg-white px-4 text-sm text-[#514740] outline-none focus:border-[#16a34a] sm:flex-none">
                            <option value="featured" @selected($sort === 'featured')>Featured</option>
                            <option value="price-asc" @selected($sort === 'price-asc')>Price: low to high</option>
                            <option value="price-desc" @selected($sort === 'price-desc')>Price: high to low</option>
                        </select>
                        <button type="submit" class="h-12 rounded-xl bg-[#14532d] px-5 text-sm font-semibold text-white transition hover:bg-[#166534]">
                            Search
                    </button>
                    </div>
                </form>

                <nav aria-label="Filter products by category" class="mt-4 flex gap-2 overflow-x-auto pb-2">
                    <a href="{{ route('home', array_filter(['q' => $search ?: null, 'sort' => $sort !== 'featured' ? $sort : null])) }}#market"
                        @class(['shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition', 'border-[#14532d] bg-[#14532d] text-white' => $selectedCategory === '', 'border-[#e8dfd5] bg-white text-[#655c56] hover:border-[#c89a8c] hover:text-[#14532d]' => $selectedCategory !== ''])>All</a>
                    @foreach ($categories as $availableCategory)
                        <a href="{{ route('home', array_filter(['q' => $search ?: null, 'category' => $availableCategory, 'sort' => $sort !== 'featured' ? $sort : null])) }}#market"
                            @class(['shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition', 'border-[#14532d] bg-[#14532d] text-white' => $selectedCategory === $availableCategory, 'border-[#e8dfd5] bg-white text-[#655c56] hover:border-[#c89a8c] hover:text-[#14532d]' => $selectedCategory !== $availableCategory])>{{ $availableCategory }}</a>
                    @endforeach
                </nav>

                @if ($products->isNotEmpty())
                    <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($products as $product)
                            <article class="group overflow-hidden rounded-2xl border border-[#ece3d9] bg-white transition duration-200 hover:-translate-y-1 hover:border-[#d8b8aa] hover:shadow-[0_16px_40px_-24px_rgba(20,83,45,.32)]">
                                <div class="relative flex aspect-[1.45] items-center justify-center overflow-hidden bg-[#f1e9df]">
                                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_50%_100%,rgba(34,197,94,.18),transparent_65%)]"></div>
                                    @if ($product->image)
                                        <img
                                            src="{{ asset('storage/'.$product->image) }}"
                                            alt="{{ $product->name }}"
                                            class="absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                            loading="lazy">
                                    @else
                                        <span class="relative flex h-20 w-20 items-center justify-center rounded-full border border-white/80 bg-white/75 text-4xl shadow-sm" aria-hidden="true">{{ match ($product->category) { 'Pork' => '🐖', 'Beef' => '🐄', 'Chicken' => '🐓', default => '🥩' } }}</span>
                                    @endif
                                    <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1.5 text-xs font-semibold text-[#14532d]">
                                        {{ $product->category }}
                                    </span>
                                    @if (! $product->is_available || $product->stock_kg < 1)
                                        <span class="absolute right-4 top-4 rounded-full bg-[#f8e9e4] px-3 py-1.5 text-xs font-semibold text-[#9f1239]">Out of Stock</span>
                                    @else
                                        <span class="absolute right-4 top-4 rounded-full bg-[#edf1e8] px-3 py-1.5 text-xs font-semibold text-[#3f6212]">Available</span>
                                    @endif
                                </div>

                                <div class="p-5">
                                    <h3 class="text-lg font-semibold tracking-tight text-[#1f2937]">{{ $product->name }}</h3>
                                    @if ($product->description)
                                        <p class="mt-1.5 min-h-10 text-sm leading-5 text-[#82766e]">{{ \Illuminate\Support\Str::limit($product->description, 76) }}</p>
                                    @else
                                        <p class="mt-1.5 min-h-10 text-sm leading-5 text-[#82766e]">A market favorite, ready for your next meal.</p>
                                    @endif
                                    <div class="mt-4 flex items-center gap-2.5 border-t border-[#f0e9e0] pt-4">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f3ebe4] text-sm font-semibold text-[#14532d]" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->producer_name ?: 'P', 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <p class="text-[11px] uppercase tracking-[.1em] text-[#978b80]">Produced by</p>
                                            <p class="truncate text-sm font-medium text-[#514740]">{{ $product->producer_name ?: 'Producer details coming soon' }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex items-end justify-between gap-3">
                                        <div>
                                            <p class="text-xs text-[#958a80]">Price per KG</p>
                                            <p class="mt-0.5 text-xl font-bold tracking-tight text-[#14532d]">₱{{ number_format((float) $product->price, 2) }}</p>
                                        </div>
                                        <span class="text-right text-xs text-[#82766e]">{{ number_format($product->stock_kg, 0) }} kg</span>
                                    </div>
                                    <button
                                        type="button"
                                        data-add-to-cart
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->price }}"
                                        data-product-stock="{{ $product->stock_kg }}"
                                        data-producer-name="{{ $product->producer_name ?: 'Producer details coming soon' }}"
                                        @disabled(! $product->is_available || $product->stock_kg < 1)
                                        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#14532d] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#166534] disabled:cursor-not-allowed disabled:bg-[#e1ddd8] disabled:text-[#827c75]"
                                    >
                                        <span aria-hidden="true">＋</span> Add to Cart
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($products->hasPages())
                        <div class="mt-10">{{ $products->links() }}</div>
                    @endif
                @else
                    <div class="mt-8 rounded-2xl border border-dashed border-[#e1d5ca] bg-[#fbf8f3] px-5 py-14 text-center">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#f2e8e1] text-2xl text-[#15803d]" aria-hidden="true">⌕</span>
                        <h3 class="mt-4 text-lg font-semibold text-[#1f2937]">No products found</h3>
                        <p class="mt-1 text-sm text-[#82766e]">Try a different search or browse all available products.</p>
                        <a href="{{ route('home') }}#market" class="mt-5 inline-flex rounded-lg bg-[#14532d] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#166534]">Clear filters</a>
                    </div>
                @endif
            </div>
        </section>
    </main>

    <dialog id="cart-dialog" class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-3xl border border-[#ece3d9] bg-[#fffdfa] p-0 text-[#292522] shadow-2xl backdrop:bg-[#24191a]/50">
        <div class="flex items-center justify-between border-b border-[#ece3d9] px-5 py-4 sm:px-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.14em] text-[#4f7657]">Your selection</p>
                <h2 class="mt-1 text-xl font-semibold text-[#1f2937]">Shopping cart</h2>
            </div>
            <button type="button" data-close-cart aria-label="Close shopping cart" class="flex h-10 w-10 items-center justify-center rounded-full text-xl text-[#655c56] transition hover:bg-[#f2e8e1] hover:text-[#14532d]">×</button>
        </div>
        <div id="cart-items" class="max-h-[55vh] min-h-28 space-y-3 overflow-y-auto px-5 py-4 sm:px-6" aria-live="polite">
            <p data-cart-empty class="py-8 text-center text-sm text-[#82766e]">Your cart is waiting for something good.</p>
        </div>
        <div class="border-t border-[#ece3d9] bg-[#fbf8f3] px-5 py-4 sm:px-6">
            <div class="flex items-center justify-between text-sm text-[#655c56]">
                <span>Estimated total <span class="text-xs">(by kg)</span></span>
                <strong id="cart-total" class="text-lg text-[#14532d]">₱0.00</strong>
            </div>
            <p class="mt-1 text-xs text-[#82766e]">Each item adds 1 kg. Final amount depends on weighed quantity.</p>
            <button type="button" data-close-cart class="mt-4 w-full rounded-xl bg-[#14532d] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#166534]">Continue shopping</button>
        </div>
    </dialog>

    <button id="cart-toggle" type="button" aria-haspopup="dialog" aria-controls="cart-dialog" class="fixed bottom-5 right-4 z-30 inline-flex min-h-14 items-center gap-3 rounded-full bg-[#14532d] px-5 text-sm font-semibold text-white shadow-[0_12px_35px_-12px_rgba(20,83,45,.75)] transition hover:-translate-y-0.5 hover:bg-[#166534] focus:outline-none focus:ring-4 focus:ring-[#a16b46]/40 sm:bottom-7 sm:right-7">
        <span class="text-lg" aria-hidden="true">🛒</span>
        <span>Cart</span>
        <span id="cart-count" class="flex h-6 min-w-6 items-center justify-center rounded-full bg-white px-1.5 text-xs font-bold text-[#14532d]" aria-label="0 items in cart">0</span>
    </button>

    <footer class="bg-[#1f2937] text-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 py-8 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight">meathub<span class="text-[#e7aa8c]">.</span></a>
            <p class="text-sm text-white/65">Good meals start with good meat.</p>
            <p class="text-xs text-white/50">&copy; {{ date('Y') }} MeatHub</p>
        </div>
    </footer>
</body>
</html>
