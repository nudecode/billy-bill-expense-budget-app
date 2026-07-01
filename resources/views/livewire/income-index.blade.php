<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="flex items-center bg-slate-100 border border-slate-200 rounded-full overflow-hidden">
            <button wire:click="previousMonth" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <span class="font-mono text-[12px] font-medium text-slate-800 px-3 min-w-[110px] text-center">{{ strtoupper($periodLabel) }}</span>
            <button wire:click="nextMonth" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition-colors">
            + Add Income
        </button>
    </div>
    {{-- Summary card --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-blue-500 to-blue-400 rounded-t-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 flex-shrink-0">
                    <i class="fa-regular fa-money-bill-1"></i>
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Total Income</div>
                    <div class="font-mono text-[13px] sm:text-[17px] font-bold text-blue-600">+${{ number_format($total, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Name</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Account</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Date</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Amount</th>
                        <th class="hidden sm:table-cell px-2 sm:px-5 py-3 w-24"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($instances as $income)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-[12px] font-bold text-blue-600 flex-shrink-0">
                                    {{ strtoupper(substr($income->getName(), 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-[13.5px] font-semibold text-slate-900">{{ $income->getName() }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $income->date->format('d M Y') }} · {{ $income->getFrequencyName() }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 hidden sm:table-cell">{{ $income->getAccountName() }}</td>
                        <td class="px-5 py-3.5 font-mono text-[12.5px] text-slate-700 hidden sm:table-cell">{{ $income->date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 font-mono text-[13px] font-medium text-blue-600">+${{ number_format($income->getAmount(), 2) }}</td>
                        <td class="hidden sm:table-cell px-2 sm:px-5 py-3.5">
                            <div class="flex items-center gap-1 justify-end lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                <button disabled title="Editing individual occurrences is coming soon"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-300 cursor-not-allowed">
                                    <i class="fa-regular fa-pen-to-square text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @foreach($oneOff as $income)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-[12px] font-bold text-blue-600 flex-shrink-0">
                                    {{ strtoupper(substr($income->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-[13.5px] font-semibold text-slate-900">{{ $income->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $income->income_date->format('d M Y') }} · One-off</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 hidden sm:table-cell">{{ $income->account->name }}</td>
                        <td class="px-5 py-3.5 font-mono text-[12.5px] text-slate-700 hidden sm:table-cell">{{ $income->income_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 font-mono text-[13px] font-medium text-blue-600">+${{ number_format($income->amount, 2) }}</td>
                        <td class="hidden sm:table-cell px-2 sm:px-5 py-3.5">
                            <div class="flex items-center gap-1 justify-end lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                <button disabled title="Editing individual occurrences is coming soon"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-300 cursor-not-allowed">
                                    <i class="fa-regular fa-pen-to-square text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @if(count($instances) === 0 && $oneOff->isEmpty())
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-[13px]">No income recorded for this period.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
