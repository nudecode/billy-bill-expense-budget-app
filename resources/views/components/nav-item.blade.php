@props(['route'])
@php $active = request()->routeIs($route); @endphp

<div x-data class="relative group/item">
    <a
        href="{{ route($route) }}"
        @click="$store.nav.mobileOpen = false"
        class="flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150 overflow-hidden whitespace-nowrap
            {{ $active
                ? 'bg-green-50 text-green-700'
                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' }}"
    >
        {{-- Icon --}}
        <span class="flex-shrink-0 {{ $active ? 'text-green-600' : 'text-gray-400 group-hover/item:text-gray-600' }}">
            {{ $icon }}
        </span>

        {{-- Label: hidden when sidebar is collapsed --}}
        <span x-show="!$store.nav.collapsed" x-cloak>{{ $slot }}</span>
    </a>

    {{-- Tooltip: only visible on desktop when sidebar is collapsed --}}
    <div
        x-show="$store.nav.collapsed"
        x-cloak
        class="nav-tooltip absolute left-[calc(100%+8px)] top-1/2 -translate-y-1/2 px-2.5 py-1.5 bg-gray-900 text-white text-xs font-medium rounded-lg pointer-events-none whitespace-nowrap z-50 hidden lg:block opacity-0 group-hover/item:opacity-100 transition-opacity duration-150"
    >
        {{ $slot }}
    </div>
</div>
