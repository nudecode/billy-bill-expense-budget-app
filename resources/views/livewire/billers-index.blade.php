<div class="relative">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="relative">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search billers…" class="pl-9 pr-4 py-1.5 border border-slate-200 rounded-lg text-[13px] bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 outline-none w-52">
        </div>
        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition-colors">
            <i class="fa-solid fa-plus text-xs"></i> Add Biller
        </button>
    </div>

    {{-- A-Z scrubber — desktop only, hidden when searching --}}
    @if(!$search && count($presentLetters) > 0)
    <div class="fixed right-3 top-1/2 -translate-y-1/2 z-10 hidden lg:flex flex-col gap-px">
        @foreach(range('A', 'Z') as $letter)
            @if(in_array($letter, $presentLetters))
                <a href="#biller-{{ $letter }}" class="w-5 h-5 flex items-center justify-center text-[10px] font-bold text-green-600 hover:bg-green-50 rounded transition-colors">{{ $letter }}</a>
            @else
                <span class="w-5 h-5 flex items-center justify-center text-[10px] text-gray-300">{{ $letter }}</span>
            @endif
        @endforeach
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Name</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Email</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden md:table-cell">Phone</th>
                        <th class="text-right px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Total Paid</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @if($grouped)
                        {{-- Grouped by letter --}}
                        @foreach($grouped as $letter => $group)
                        <tr id="biller-{{ $letter }}" class="bg-gray-50 border-y border-slate-100">
                            <td colspan="5" class="px-5 py-2">
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">{{ $letter }}</span>
                            </td>
                        </tr>
                        @foreach($group as $biller)
                        @include('livewire.partials.biller-row', ['biller' => $biller])
                        @endforeach
                        @endforeach
                    @else
                        {{-- Flat list when searching --}}
                        @forelse($billers as $biller)
                            @include('livewire.partials.biller-row', ['biller' => $biller])
                        @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-[13px]">No billers match your search.</td></tr>
                        @endforelse
                    @endif

                    @if($billers->isEmpty())
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-[13px]">No billers yet. Add your first biller to get started.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
