<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="relative">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search…" class="pl-9 pr-4 py-1.5 border border-slate-200 rounded-lg text-[13px] bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 outline-none w-52">
        </div>
        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 text-white text-[13px] font-semibold rounded-lg hover:bg-slate-700 transition-colors">
            + Add Recurring Bill
        </button>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Biller</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Frequency</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Amount</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden md:table-cell">Start Date</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden md:table-cell">End Date</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rules as $rule)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[12px] font-bold flex-shrink-0"
                                     style="background: hsl({{ crc32($rule->biller->name) % 360 }}, 70%, 92%); color: hsl({{ crc32($rule->biller->name) % 360 }}, 60%, 35%)">
                                    {{ strtoupper(substr($rule->biller->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-[13.5px] font-semibold text-slate-900">{{ $rule->biller->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $rule->category->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 hidden sm:table-cell">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-blue-50 text-blue-600">{{ $rule->frequency->name }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-[13px] font-medium text-slate-900">${{ number_format($rule->amount, 2) }}</td>
                        <td class="px-5 py-3.5 font-mono text-[12.5px] text-slate-500 hidden md:table-cell">{{ $rule->start_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 font-mono text-[12.5px] text-slate-400 hidden md:table-cell">{{ $rule->end_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1 justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all text-sm">✏️</button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all text-sm">🗑</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400 text-[13px]">No recurring bills yet. Add one to get started.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
