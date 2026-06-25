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
                    <tr class="hover:bg-slate-50/50 cursor-pointer transition-colors" wire:click="viewPayment({{ $payment->id }})">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[12px] font-bold flex-shrink-0"
                                     style="background: hsl({{ crc32($payment->biller->name) % 360 }}, 70%, 92%); color: hsl({{ crc32($payment->biller->name) % 360 }}, 60%, 35%)">
                                    {{ strtoupper(substr($payment->biller->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-[13.5px] font-semibold text-slate-900">{{ $payment->biller->name }}</div>
                                    <div class="text-[11.5px] text-slate-400 mt-0.5">
                                        Paid {{ $payment->payment_date->format('d M Y') }}@if($payment->reference_number) · {{ $payment->reference_number }}@endif
                                    </div>
                                </div>
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

    {{-- ── PAYMENT DETAIL MODAL ─────────────────────────────────────── --}}
    @if($showDetailModal && $viewingPayment)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.closeDetailModal()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.closeDetailModal()"></div>

        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-[13px] font-bold flex-shrink-0"
                         style="background: hsl({{ crc32($viewingPayment->biller->name) % 360 }}, 70%, 92%); color: hsl({{ crc32($viewingPayment->biller->name) % 360 }}, 60%, 35%)">
                        {{ strtoupper(substr($viewingPayment->biller->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-[15px] font-bold text-slate-900">{{ $viewingPayment->biller->name }}</div>
                        <div class="text-[11.5px] text-slate-400">{{ $viewingPayment->category->name }}</div>
                    </div>
                </div>
                <button wire:click="closeDetailModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Amount banner --}}
            <div class="px-5 py-4 bg-emerald-50 border-b border-emerald-100 flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-widest text-emerald-600">Amount Paid</span>
                <span class="font-mono text-[22px] font-bold text-emerald-700">${{ number_format($viewingPayment->amount, 2) }}</span>
            </div>

            {{-- Details --}}
            <div class="px-5 py-4 space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-slate-50">
                    <span class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide">Paid On</span>
                    <span class="font-mono text-[13px] text-slate-800">{{ $viewingPayment->payment_date->format('d M Y') }}</span>
                </div>
                @if($viewingPayment->recurring_bill_date && $viewingPayment->recurring_bill_date->toDateString() !== $viewingPayment->payment_date->toDateString())
                <div class="flex items-center justify-between py-2 border-b border-slate-50">
                    <span class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide">Bill Due Date</span>
                    <span class="font-mono text-[13px] text-slate-800">{{ $viewingPayment->recurring_bill_date->format('d M Y') }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between py-2 border-b border-slate-50">
                    <span class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide">Account</span>
                    <span class="text-[13px] text-slate-800">{{ $viewingPayment->account->name }}</span>
                </div>
                @if($viewingPayment->reference_number)
                <div class="flex items-center justify-between py-2 border-b border-slate-50">
                    <span class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide">Reference No.</span>
                    <span class="font-mono text-[13px] text-slate-800">{{ $viewingPayment->reference_number }}</span>
                </div>
                @endif
                @if($viewingPayment->notes)
                <div class="py-2">
                    <span class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide block mb-1.5">Notes</span>
                    <p class="text-[13px] text-slate-700 leading-relaxed">{{ $viewingPayment->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-slate-100">
                <button wire:click="closeDetailModal()" type="button"
                        class="w-full px-4 py-2.5 text-[13px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
