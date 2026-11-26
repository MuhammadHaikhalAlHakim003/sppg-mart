
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SPPG MART')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f4f9fc] text-slate-900 antialiased">
    @php($isLanding = request()->routeIs('home') && (! auth()->check() || ! auth()->user()->isBuyer()))
    <header class="{{ $isLanding ? 'relative z-50 border-b border-white/15 bg-[#0c5f83] lg:absolute lg:inset-x-0 lg:top-0 lg:bg-[#0c5f83]/35' : 'sticky top-0 z-50 border-b border-orange-100 bg-white/95 shadow-sm backdrop-blur' }}">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex min-h-16 flex-wrap items-center justify-between gap-x-1 gap-y-2 py-2 sm:min-h-20 sm:gap-x-3 sm:gap-y-3 sm:py-3">
                <a href="{{ route('home') }}" aria-label="SPPG MART - Beranda" class="flex shrink-0 items-center">
                    <img src="{{ asset('images/sppg-mart-fresh-delivery-logo.png') }}" alt="SPPG MART - Belanja Kebutuhan Pangan, Lebih Mudah" class="h-auto w-24 max-w-[42vw] object-contain min-[380px]:w-28 min-[520px]:w-40 sm:w-56 lg:w-60">
                </a>

                @if ($isLanding && (! auth()->check() || ! auth()->user()->isBuyer()))
                    <nav class="hidden items-center gap-7 text-sm font-bold text-white/80 xl:flex">
                        <a href="{{ route('home') }}" class="text-white transition hover:text-white">Beranda</a>
                        <a href="{{ route('catalog') }}" class="transition hover:text-white">Katalog</a>
                        <a href="{{ route('home') }}#keunggulan" class="transition hover:text-white">Keunggulan</a>
                        <a href="{{ route('home') }}#faq" class="transition hover:text-white">Bantuan</a>
                    </nav>
                @else
                    <form action="{{ route('catalog') }}" method="GET" class="order-3 hidden w-full md:order-2 md:flex md:max-w-xl md:flex-1">
                        @if (request()->routeIs('catalog') && request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari beras, sayur, protein..." class="min-w-0 flex-1 rounded-l-lg border-2 border-orange-500 px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-orange-200">
                        <button type="submit" aria-label="Cari produk" class="flex w-10 shrink-0 items-center justify-center rounded-r-lg bg-orange-600 text-white hover:bg-orange-700">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                <circle cx="10.8" cy="10.8" r="6.8"></circle>
                                <path d="m16 16 4.5 4.5"></path>
                            </svg>
                        </button>
                    </form>
                @endif

                <div class="order-2 ml-auto flex max-w-full flex-wrap items-center justify-end gap-0.5 sm:gap-2 md:order-3">
                    <details class="relative md:hidden">
                        <summary aria-label="Buka pencarian" title="Cari produk" class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-lg {{ $isLanding ? 'text-white hover:bg-white/10' : 'text-slate-600 hover:bg-sky-50' }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                <circle cx="10.8" cy="10.8" r="6.8"></circle>
                                <path d="m16 16 4.5 4.5"></path>
                            </svg>
                        </summary>
                        <form action="{{ route('catalog') }}" method="GET" role="search" class="fixed inset-x-4 top-[4.5rem] z-[60] mx-auto flex max-w-sm overflow-hidden rounded-xl border border-sky-200 bg-white p-1 shadow-xl">
                            @if (request()->routeIs('catalog') && request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            <input type="search" name="search" value="{{ request('search') }}" aria-label="Cari produk" placeholder="Cari produk..." class="min-w-0 flex-1 bg-transparent px-3 py-2 text-sm text-slate-800 outline-none">
                            <button type="submit" aria-label="Jalankan pencarian" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#167ba5] text-white hover:bg-[#105f82]">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                    <circle cx="10.8" cy="10.8" r="6.8"></circle>
                                    <path d="m16 16 4.5 4.5"></path>
                                </svg>
                            </button>
                        </form>
                    </details>

                    @if ($isLanding && (! auth()->check() || ! auth()->user()->isBuyer()))
                        <form action="{{ route('catalog') }}" method="GET" role="search" class="hidden h-10 w-40 overflow-hidden rounded-lg border border-white/35 bg-white/95 text-slate-800 shadow-sm md:flex md:w-44 xl:w-52">
                            <input type="search" name="search" value="{{ request('search') }}" aria-label="Cari produk" placeholder="Cari produk..." class="min-w-0 flex-1 bg-transparent px-3 text-xs outline-none placeholder:text-slate-500 sm:text-sm">
                            <button type="submit" aria-label="Jalankan pencarian" class="flex w-9 shrink-0 items-center justify-center text-[#126586] transition hover:bg-sky-50">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                    <circle cx="10.8" cy="10.8" r="6.8"></circle>
                                    <path d="m16 16 4.5 4.5"></path>
                                </svg>
                            </button>
                        </form>
                    @endif

                    {{-- MENU BARU: ASISTEN AI --}}
                    <a href="{{ route('chatbot.index') }}"
                       aria-label="Asisten AI"
                       title="Asisten AI"
                       class="inline-flex min-h-9 min-w-9 items-center justify-center gap-1.5 rounded-lg px-1 py-2 text-sm font-semibold transition sm:min-h-10 sm:min-w-10 sm:px-3 {{ request()->routeIs('chatbot.*') ? ($isLanding ? 'bg-white/15 text-white' : 'bg-sky-50 text-sky-700') : ($isLanding ? 'text-white hover:bg-white/10' : 'text-slate-600 hover:bg-sky-50') }}">

                        <svg aria-hidden="true"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round"
                             class="h-5 w-5 shrink-0">
                            <path d="m12 3 1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3Z"/>
                            <path d="m19 14 1.1 2.9L23 18l-2.9 1.1L19 22l-1.1-2.9L15 18l2.9-1.1L19 14Z"/>
                        </svg>

                        <span class="sr-only md:not-sr-only">Asisten AI</span>
                    </a>
                    {{-- AKHIR MENU ASISTEN AI --}}

                    @auth
                        @if (auth()->user()->isBuyer())
                            @php($cartCount = \App\Models\Cart::where('user_id', auth()->id())->where('status', 'active')->withSum('items', 'quantity')->value('items_sum_quantity') ?? 0)
                            <a href="{{ route('chat.index') }}" aria-label="Chat" title="Chat" class="inline-flex min-h-9 min-w-9 items-center justify-center gap-1.5 rounded-lg px-1 py-2 text-sm font-semibold {{ $isLanding ? 'text-white hover:bg-white/10' : 'text-slate-600 hover:bg-orange-50' }} sm:min-h-10 sm:min-w-10 sm:px-3">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0"><path d="M21 11.5a8.4 8.4 0 0 1-8.5 8.4 8.7 8.7 0 0 1-4-.9L3 20l1.1-4.1a8.1 8.1 0 0 1-1.1-4.2A8.4 8.4 0 0 1 11.5 3h1A8.4 8.4 0 0 1 21 11.5Z"></path></svg>
                                <span class="sr-only md:not-sr-only">Chat</span>
                            </a>

                            <a href="{{ route('cart.index') }}" aria-label="Keranjang" title="Keranjang" class="relative inline-flex min-h-9 min-w-9 items-center justify-center gap-1.5 rounded-lg px-1 py-2 text-sm font-semibold {{ $isLanding ? 'text-white hover:bg-white/10' : 'text-orange-700 hover:bg-orange-50' }} sm:min-h-10 sm:min-w-10 sm:px-3">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 1.9-1.4L22 8H6"></path><circle cx="10" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle></svg>
                                <span class="sr-only md:not-sr-only">Keranjang</span>
                                @if ($cartCount > 0)
                                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-orange-600 px-1 text-[10px] font-bold text-white">{{ $cartCount }}</span>
                                @endif
                            </a>

                            <a href="{{ route('transactions.index') }}" aria-label="Pesanan" title="Pesanan" class="inline-flex min-h-9 min-w-9 items-center justify-center gap-1.5 rounded-lg px-1 py-2 text-sm font-semibold {{ $isLanding ? 'text-white hover:bg-white/10' : 'text-slate-600 hover:bg-orange-50' }} sm:min-h-10 sm:min-w-10 sm:px-3">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0"><path d="M7 3h10a2 2 0 0 1 2 2v16l-7-3-7 3V5a2 2 0 0 1 2-2Z"></path><path d="M9 8h6M9 12h6"></path></svg>
                                <span class="sr-only md:not-sr-only">Pesanan</span>
                            </a>
                        @else
                            @if (auth()->user()->isSupplier())
                                <a href="{{ route('chat.index') }}" aria-label="Chat" title="Chat" class="inline-flex min-h-9 min-w-9 items-center justify-center gap-1.5 rounded-lg px-1 py-2 text-sm font-semibold {{ $isLanding ? 'text-white hover:bg-white/10' : 'text-slate-600 hover:bg-orange-50' }} sm:min-h-10 sm:min-w-10 sm:px-3">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0"><path d="M21 11.5a8.4 8.4 0 0 1-8.5 8.4 8.7 8.7 0 0 1-4-.9L3 20l1.1-4.1a8.1 8.1 0 0 1-1.1-4.2A8.4 8.4 0 0 1 11.5 3h1A8.4 8.4 0 0 1 21 11.5Z"></path></svg>
                                    <span class="sr-only md:not-sr-only">Chat</span>
                                </a>
                            @endif
                            <a href="{{ route('dashboard') }}" aria-label="Dashboard" title="Dashboard" class="inline-flex min-h-9 min-w-9 items-center justify-center gap-1.5 rounded-lg px-1 py-2 text-sm font-semibold {{ $isLanding ? 'text-white hover:bg-white/10' : 'text-slate-600 hover:bg-orange-50' }} sm:min-h-10 sm:min-w-10 sm:px-3">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0"><rect x="3" y="3" width="8" height="8" rx="1.5"></rect><rect x="13" y="3" width="8" height="5" rx="1.5"></rect><rect x="13" y="10" width="8" height="11" rx="1.5"></rect><rect x="3" y="13" width="8" height="8" rx="1.5"></rect></svg>
                                <span class="sr-only md:not-sr-only">Dashboard</span>
                            </a>
                        @endif

                        <a href="{{ route('profile.edit') }}" aria-label="Profil" title="Profil" class="inline-flex min-h-9 min-w-9 items-center justify-center gap-1.5 rounded-lg px-1 py-2 text-sm font-semibold {{ $isLanding ? 'text-white hover:bg-white/10' : 'text-slate-600 hover:bg-orange-50' }} sm:min-h-10 sm:min-w-10 sm:px-3">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0"><circle cx="12" cy="8" r="4"></circle><path d="M4.5 21a7.5 7.5 0 0 1 15 0"></path></svg>
                            <span class="sr-only md:not-sr-only">Profil</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" aria-label="Keluar" title="Keluar" class="inline-flex min-h-9 min-w-9 items-center justify-center gap-1.5 rounded-lg border px-1 py-2 text-sm font-semibold {{ $isLanding ? 'border-white/30 text-white hover:bg-white/10' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }} sm:min-h-10 sm:min-w-10 sm:px-3">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0"><path d="M10 17l5-5-5-5M15 12H3"></path><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"></path></svg>
                                <span class="sr-only md:not-sr-only">Keluar</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('register') }}" aria-label="Buat Akun" title="Buat Akun" class="inline-flex min-h-9 min-w-9 items-center justify-center gap-1.5 rounded-lg border px-1 py-2 text-sm font-bold transition sm:min-h-10 sm:min-w-10 sm:px-4 {{ $isLanding ? 'border-white/40 bg-white/10 text-white hover:bg-white/20' : 'border-orange-600 bg-orange-600 text-white hover:bg-orange-700' }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0"><circle cx="9" cy="8" r="4"></circle><path d="M2.5 21a6.5 6.5 0 0 1 13 0M19 8v6M16 11h6"></path></svg>
                            <span class="sr-only md:not-sr-only">Buat Akun</span>
                        </a>

                        <a href="{{ route('login') }}" aria-label="Masuk" title="Masuk" class="inline-flex min-h-9 min-w-9 items-center justify-center gap-1.5 rounded-lg px-1 py-2 text-sm font-bold shadow-sm transition sm:min-h-10 sm:min-w-10 sm:px-4 {{ $isLanding ? 'bg-white text-sky-700 hover:bg-sky-50' : 'bg-orange-600 text-white hover:bg-orange-700' }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0"><path d="M10 17l5-5-5-5M15 12H3"></path><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"></path></svg>
                            <span class="sr-only md:not-sr-only">Masuk</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-slate-900 text-slate-100">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-8 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <img src="{{ asset('images/sppg-mart-fresh-delivery-logo.png') }}" alt="SPPG MART - Belanja Kebutuhan Pangan, Lebih Mudah" class="mb-3 h-auto w-52 max-w-full object-contain">
                    <p class="text-sm text-slate-300">Marketplace pengadaan bahan baku pangan yang mempertemukan SPPG dengan pemasok terpercaya.</p>
                </div>
                <div>
                    <h3 class="mb-3 font-semibold text-white">Navigasi</h3>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li><a href="{{ route('catalog') }}">Katalog Produk</a></li>
                        <li><a href="{{ route('home') }}#keunggulan">Keunggulan</a></li>
                        <li><a href="{{ route('home') }}#faq">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-3 font-semibold text-white">Bantuan</h3>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li>support@sppgmart.id</li>
                        <li>+62 21 555 2026</li>
                        <li>Senin - Jumat / 08:00 - 17:00</li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-3 font-semibold text-white">Legal</h3>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li><a href="{{ route('privacy') }}">Kebijakan Privasi</a></li>
                        <li><a href="{{ route('terms') }}">Ketentuan Layanan</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 border-t border-slate-700 pt-6 text-center text-sm text-slate-400">© {{ date('Y') }} SPPG MART. Semua hak dilindungi.</div>
        </div>
    </footer>

    <div data-page-loading role="status" aria-live="polite" aria-hidden="true" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
        <div class="flex w-full max-w-sm flex-col items-center rounded-3xl border border-white/70 bg-white/95 px-8 py-9 text-center shadow-2xl">
            <img src="{{ asset('images/sppg-mart-fresh-delivery-logo.png') }}" alt="" class="loading-dance h-auto w-56 max-w-[70vw] object-contain">
            <p class="mt-5 text-sm font-bold tracking-wide text-[#126586]">Loading, mohon tunggu...</p>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
