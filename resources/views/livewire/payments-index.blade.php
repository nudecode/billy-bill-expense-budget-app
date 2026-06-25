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
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Biller</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Category</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Date</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Amount</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden md:table-cell">Account</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[12px] font-bold flex-shrink-0"
                                     style="background: hsl({{ crc32($payment->biller->name) % 360 }}, 70%, 92%); color: hsl({{ crc32($payment->biller->name) % 360 }}, 60%, 35%)">
                                    {{ strtoupper(substr($payment->biller->name, 0, 1)) }}
                                </div>
                                <div class="text-[13.5px] font-semibold text-slate-900">{{ $payment->biller->name }}</div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 hidden sm:table-cell"><span class="inline-flex px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-slate-100 text-slate-600 border border-slate-200">{{ $payment->category->name }}</span></td>
                        <td class="px-5 py-3.5 font-mono text-[12.5px] text-slate-700">{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 font-mono text-[13px] font-medium text-slate-900">${{ number_format($payment->amount, 2) }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 hidden md:table-cell">{{ $payment->account->name }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-[13px]">No payments recorded for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 bg-slate-50 border-t-2 border-slate-200 flex justify-end">
            <div class="text-right"><div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Paid</div><div class="font-mono text-[14px] font-semibold text-slate-900">${{ number_format($total, 2) }}</div></div>
        </div>
    </div>
</div>
