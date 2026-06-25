<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Billy') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        /* Tooltip arrow */
        .nav-tooltip::before {
            content: '';
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #111827;
        }
    </style>
</head>
<body class="bg-gray-50 antialiased font-sans">

{{-- ═══ MOBILE SCRIM ═══ --}}
<div
    x-data
    x-show="$store.nav.mobileOpen"
    x-cloak
    x-transition.opacity.duration.200ms
    @click="$store.nav.mobileOpen = false"
    class="fixed inset-0 bg-black/40 z-20 lg:hidden"
></div>

{{-- ═══ SIDEBAR ═══ --}}
<aside
    x-data
    :class="{
        'w-64': !$store.nav.collapsed,
        'w-16': $store.nav.collapsed,
        'translate-x-0': $store.nav.mobileOpen,
        '-translate-x-full': !$store.nav.mobileOpen
    }"
    class="fixed inset-y-0 left-0 z-30 flex flex-col bg-white border-r border-gray-200 transition-all duration-300 ease-in-out lg:translate-x-0"
>
    {{-- Logo + collapse toggle --}}
    <div x-data class="flex items-center h-16 px-4 border-b border-gray-100 flex-shrink-0 overflow-hidden gap-2">
        <div class="w-8 h-8 rounded-lg bg-green-600 flex items-center justify-center text-white flex-shrink-0 shadow-sm shadow-green-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <span x-show="!$store.nav.collapsed" x-cloak x-transition:enter="transition-opacity duration-200 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="font-bold text-gray-900 text-[15px] tracking-tight whitespace-nowrap">Billy</span>
        <span x-show="!$store.nav.collapsed" x-cloak class="font-mono text-[10px] text-gray-400 whitespace-nowrap">budget</span>

        {{-- Collapse toggle — desktop only, sits at top-right of logo row --}}
        <button
            @click="$store.nav.toggle()"
            :title="$store.nav.collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            class="hidden lg:flex ml-auto p-1.5 rounded-lg text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-colors flex-shrink-0"
        >
            <svg :class="$store.nav.collapsed ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-2 py-4 overflow-y-auto overflow-x-hidden space-y-0.5">

        <x-nav-item route="dashboard">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg></x-slot>
            Dashboard
        </x-nav-item>

        {{-- Section label --}}
        <div x-show="!$store.nav.collapsed" x-cloak class="pt-4 pb-1 px-3">
            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Bills</span>
        </div>
        <div x-show="$store.nav.collapsed" x-cloak class="pt-3 pb-1 border-t border-gray-100 mx-2"></div>

        <x-nav-item route="bills">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg></x-slot>
            Bills
        </x-nav-item>

        <x-nav-item route="recurring-bills">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg></x-slot>
            Recurring Bills
        </x-nav-item>

        {{-- Section label --}}
        <div x-show="!$store.nav.collapsed" x-cloak class="pt-4 pb-1 px-3">
            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Income</span>
        </div>
        <div x-show="$store.nav.collapsed" x-cloak class="pt-3 pb-1 border-t border-gray-100 mx-2"></div>

        <x-nav-item route="income">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg></x-slot>
            Income
        </x-nav-item>

        <x-nav-item route="recurring-income">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg></x-slot>
            Recurring Income
        </x-nav-item>

        {{-- Section label --}}
        <div x-show="!$store.nav.collapsed" x-cloak class="pt-4 pb-1 px-3">
            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Manage</span>
        </div>
        <div x-show="$store.nav.collapsed" x-cloak class="pt-3 pb-1 border-t border-gray-100 mx-2"></div>

        <x-nav-item route="billers">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg></x-slot>
            Billers
        </x-nav-item>

        <x-nav-item route="payments">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot>
            Payments
        </x-nav-item>

        <x-nav-item route="accounts">
            <x-slot name="icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg></x-slot>
            Accounts
        </x-nav-item>

    </nav>

    {{-- User footer --}}
    <div x-data class="flex-shrink-0 border-t border-gray-100 p-2 overflow-hidden">
        <div class="flex items-center gap-2.5 px-2 py-2 rounded-lg hover:bg-gray-50 transition-colors cursor-default">
            <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center text-green-700 text-xs font-bold flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div x-show="!$store.nav.collapsed" x-cloak class="min-w-0">
                <div class="text-[13px] font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</div>
                <div class="text-[11px] text-gray-400 truncate">{{ auth()->user()->email }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" x-show="!$store.nav.collapsed" x-cloak>
            @csrf
            <button type="submit" class="w-full text-left px-3 py-1.5 text-[12px] text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition-colors mt-0.5">
                Sign out
            </button>
        </form>
    </div>
</aside>

{{-- ═══ MAIN CONTENT ═══ --}}
<div
    x-data
    :class="$store.nav.collapsed ? 'lg:ml-16' : 'lg:ml-64'"
    class="ml-0 pb-16 lg:pb-0 transition-all duration-300 min-h-screen flex flex-col"
>
    {{-- Topbar --}}
    <header class="sticky top-0 z-10 h-14 bg-white/90 backdrop-blur-md border-b border-gray-200 flex items-center justify-between px-4 lg:px-6 gap-4 flex-shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            {{-- Hamburger (mobile only) --}}
            <button
                x-data
                @click="$store.nav.mobileOpen = true"
                class="lg:hidden w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
            <h1 class="text-[15px] font-bold text-gray-900 truncate">{{ $title ?? config('app.name') }}</h1>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            {{ $actions ?? '' }}
        </div>
    </header>

    {{-- Page content --}}
    <main class="flex-1 p-4 lg:p-7">
        {{ $slot }}
    </main>
</div>

{{-- ═══ BOTTOM NAV (mobile only) ═══ --}}
<nav x-data class="fixed bottom-0 left-0 right-0 z-20 bg-white border-t border-gray-200 flex lg:hidden">

    @php
        $bottomNav = [
            ['route' => 'dashboard', 'label' => 'Home'],
            ['route' => 'bills',     'label' => 'Bills'],
            ['route' => 'income',    'label' => 'Income'],
            ['route' => 'billers',   'label' => 'Billers'],
        ];
    @endphp

    @foreach($bottomNav as $item)
    @php $active = request()->routeIs($item['route']); @endphp
    <a href="{{ route($item['route']) }}" class="flex-1 flex flex-col items-center justify-center py-2 gap-0.5 text-[10px] font-semibold transition-colors {{ $active ? 'text-green-600' : 'text-gray-400' }}">
        @if($item['route'] === 'dashboard')
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="{{ $active ? '2.25' : '1.75' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
        @elseif($item['route'] === 'bills')
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="{{ $active ? '2.25' : '1.75' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        @elseif($item['route'] === 'income')
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="{{ $active ? '2.25' : '1.75' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
        @else
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="{{ $active ? '2.25' : '1.75' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
        @endif
        <span>{{ $item['label'] }}</span>
    </a>
    @endforeach

    {{-- More --}}
    <button @click="$store.nav.mobileOpen = true" class="flex-1 flex flex-col items-center justify-center py-2 gap-0.5 text-[10px] font-semibold text-gray-400">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
        <span>More</span>
    </button>

</nav>

@livewireScripts

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('nav', {
        collapsed: localStorage.getItem('billy_nav_collapsed') === 'true',
        mobileOpen: false,
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('billy_nav_collapsed', this.collapsed);
        }
    });
});
</script>

</body>
</html>
