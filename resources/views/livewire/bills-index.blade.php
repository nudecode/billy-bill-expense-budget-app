<div>
    {{-- Month nav + actions --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-2">
            <div class="flex items-center bg-slate-100 border border-slate-200 rounded-full overflow-hidden">
                <button wire:click="previousMonth" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <span class="font-mono text-[12px] font-medium text-slate-800 px-3 min-w-[110px] text-center">{{ strtoupper($periodLabel) }}</span>
                <button wire:click="nextMonth" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 text-white text-[13px] font-semibold rounded-lg hover:bg-slate-700 transition-colors">
            + Add Bill
        </button>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 p-1 bg-slate-100 rounded-xl w-fit mb-5">
        <button wire:click="$set('tab','all')" class="px-4 py-1.5 rounded-lg text-[13px] font-semibold transition-all {{ $tab === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">All</button>
        <button wire:click="$set('tab','unpaid')" class="px-4 py-1.5 rounded-lg text-[13px] font-semibold transition-all {{ $tab === 'unpaid' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">Unpaid</button>
        <button wire:click="$set('tab','paid')" class="px-4 py-1.5 rounded-lg text-[13px] font-semibold transition-all {{ $tab === 'paid' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">Paid</button>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 flex gap-3 flex-wrap">
            <div class="relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search bills…" class="pl-9 pr-4 py-1.5 border border-slate-200 rounded-lg text-[13px] bg-slate-50 focus:bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 outline-none transition-all w-52">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Biller</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden md:table-cell">Category</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Due Date</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Amount</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $filtered = collect($instances)->filter(function($b) {
                            return $this->tab === 'all'
                                || ($this->tab === 'paid' && $b->isPaid)
                                || ($this->tab === 'unpaid' && !$b->isPaid);
                        });
                    @endphp
                    @forelse($filtered as $bill)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[12px] font-bold flex-shrink-0"
                                     style="background: hsl({{ crc32($bill->getBillerName()) % 360 }}, 70%, 92%); color: hsl({{ crc32($bill->getBillerName()) % 360 }}, 60%, 35%)">
                                    {{ strtoupper(substr($bill->getBillerName(), 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-[13.5px] font-semibold text-slate-900">{{ $bill->getBillerName() }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $bill->getFrequencyName() }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 hidden md:table-cell">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-slate-100 text-slate-600 border border-slate-200">{{ $bill->getCategoryName() }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-[12.5px] text-slate-700">{{ $bill->date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 font-mono text-[13px] font-medium text-slate-900">${{ number_format($bill->getAmount(), 2) }}</td>
                        <td class="px-5 py-3.5">
                            @if($bill->isPaid)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-emerald-50 text-emerald-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>Paid
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-red-50 text-red-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>Due
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1 justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                                @if(!$bill->isPaid)
                                <button wire:click="markPaid({{ $bill->rule->id }}, '{{ $bill->date->toDateString() }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all text-sm"
                                        title="Mark Paid">💳</button>
                                @endif
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all text-sm" title="Edit">✏️</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400 text-[13px]">No bills found for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Footer totals --}}
        <div class="px-5 py-3 bg-slate-50 border-t-2 border-slate-200 flex justify-end gap-7">
            <div class="text-right"><div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Paid</div><div class="font-mono text-[14px] font-semibold text-emerald-600">${{ number_format($paidTotal, 2) }}</div></div>
            <div class="text-right"><div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Unpaid</div><div class="font-mono text-[14px] font-semibold text-red-600">${{ number_format($unpaidTotal, 2) }}</div></div>
            <div class="text-right"><div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total</div><div class="font-mono text-[14px] font-semibold text-slate-900">${{ number_format($paidTotal + $unpaidTotal, 2) }}</div></div>
        </div>
    </div>
</div>
