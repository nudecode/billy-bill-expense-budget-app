@props(['route', 'icon' => ''])
@php $active = request()->routeIs($route); @endphp
<a
    href="{{ route($route) }}"
    @click="sidebarOpen = false"
    class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium mb-0.5 transition-all duration-150
        {{ $active
            ? 'bg-white/10 text-white'
            : 'text-white/50 hover:bg-white/[0.06] hover:text-white/85' }}"
>
    <span class="w-5 text-center flex-shrink-0 text-[15px] opacity-80">{{ $icon }}</span>
    {{ $slot }}
</a>
