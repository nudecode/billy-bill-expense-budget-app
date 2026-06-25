<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Billy') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-slate-100" x-data="{ sidebarOpen: false }">

    {{-- Mobile scrim --}}
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-slate-900/50 z-20 lg:hidden"
    ></div>

    {{-- ═══ SIDEBAR ═══ --}}
    <aside
        class="fixed top-0 left-0 bottom-0 w-64 bg-slate-900 z-30 flex flex-col transition-transform duration-300 lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-6 border-b border-white/[0.06] flex-shrink-0">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-lg shadow-lg shadow-emerald-500/30 flex-shrink-0">
                💰
            </div>
            <div>
                <div class="text-white font-bold text-[15px] tracking-tight leading-tight">Billy</div>
                <div class="text-white/30 text-[10px] font-mono mt-0.5">Budget Tracker</div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 overflow-y-auto">
            <x-nav-item route="dashboard" icon="⊞">Dashboard</x-nav-item>

            <div class="text-white/25 text-[10px] font-semibold uppercase tracking-widest px-2.5 pt-4 pb-1.5">Bills</div>
            <x-nav-item route="bills" icon="📄">Bills</x-nav-item>
            <x-nav-item route="recurring-bills" icon="🔁">Recurring Bills</x-nav-item>

            <div class="text-white/25 text-[10px] font-semibold uppercase tracking-widest px-2.5 pt-4 pb-1.5">Income</div>
            <x-nav-item route="income" icon="💵">Income</x-nav-item>
            <x-nav-item route="recurring-income" icon="🔁">Recurring Income</x-nav-item>

            <div class="text-white/25 text-[10px] font-semibold uppercase tracking-widest px-2.5 pt-4 pb-1.5">Manage</div>
            <x-nav-item route="billers" icon="🏢">Billers</x-nav-item>
            <x-nav-item route="payments" icon="✅">Payments</x-nav-item>
            <x-nav-item route="accounts" icon="🏦">Accounts</x-nav-item>
        </nav>

        {{-- User footer --}}
        <div class="px-3 py-3 border-t border-white/[0.06] flex-shrink-0">
            <div class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg hover:bg-white/5 transition-colors">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-white/80 text-[13px] font-semibold truncate">{{ auth()->user()->name }}</div>
                    <div class="text-white/30 text-[11px] truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="w-full text-left px-2.5 py-1.5 text-white/30 text-[12px] hover:text-white/60 transition-colors rounded-lg hover:bg-white/5">
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    {{-- ═══ MAIN CONTENT ═══ --}}
    <div class="lg:ml-64 min-h-screen flex flex-col">

        {{-- Topbar --}}
        <header class="sticky top-0 z-10 h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-5 lg:px-8 gap-4 flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                {{-- Hamburger --}}
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-[15px] font-bold text-slate-900 truncate">{{ $title ?? config('app.name') }}</h1>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                {{ $actions ?? '' }}
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-5 lg:p-8">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
