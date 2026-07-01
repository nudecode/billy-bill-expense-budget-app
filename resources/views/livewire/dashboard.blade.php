<div>
    {{-- Month Navigator --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="flex items-center bg-slate-100 border border-slate-200 rounded-full overflow-hidden">
                <button wire:click="previousMonth" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-200 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <span class="font-mono text-[12px] font-medium text-slate-800 px-3 min-w-[110px] text-center">{{ strtoupper($periodLabel) }}</span>
                <button wire:click="nextMonth" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-200 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('bills') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition-colors">
                + Add Bill
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-7">
        {{-- Total Bills --}}
        <a href="{{ route('bills') }}" class="group bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-amber-500 to-amber-400 rounded-t-2xl"></div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500"><i class="fa-regular fa-file-lines text-base"></i></div>
                <span class="font-mono text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{{ $paidCount + $unpaidCount }} bills</span>
            </div>
            <div class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Bills</div>
            <div class="font-mono text-[20px] font-bold text-slate-900 tracking-tight">${{ number_format($totalBills, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">this month</div>
            <div class="h-1 bg-slate-100 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full" style="width:{{ $totalIncome > 0 ? min(100, ($totalBills/$totalIncome)*100) : 0 }}%"></div>
            </div>
        </a>

        {{-- Total Income --}}
        <a href="{{ route('income') }}" class="group bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-blue-600 to-blue-400 rounded-t-2xl"></div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500"><i class="fa-regular fa-money-bill-1 text-base"></i></div>
                <span class="font-mono text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">income</span>
            </div>
            <div class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Income</div>
            <div class="font-mono text-[20px] font-bold text-slate-900 tracking-tight">${{ number_format($totalIncome, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">this month</div>
            <div class="h-1 bg-slate-100 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-blue-600 rounded-full" style="width:100%"></div>
            </div>
        </a>

        {{-- Bills Paid --}}
        <a href="{{ route('payments') }}" class="group bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-emerald-600 to-emerald-400 rounded-t-2xl"></div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600"><i class="fa-regular fa-circle-check text-base"></i></div>
                <span class="font-mono text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{{ $paidCount }} of {{ $paidCount + $unpaidCount }}</span>
            </div>
            <div class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">Bills Paid</div>
            <div class="font-mono text-[20px] font-bold text-slate-900 tracking-tight">{{ $paidCount }}</div>
            <div class="text-[11px] text-slate-400 mt-1">${{ number_format($paidAmount, 2) }} paid</div>
            <div class="h-1 bg-slate-100 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-emerald-600 rounded-full" style="width:{{ ($paidCount + $unpaidCount) > 0 ? ($paidCount/($paidCount+$unpaidCount))*100 : 0 }}%"></div>
            </div>
        </a>

        {{-- Unpaid --}}
        <a href="{{ route('bills', ['tab' => 'unpaid', 'selectedDate' => '', 'y' => $year, 'm' => $month]) }}" class="group bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-red-600 to-red-400 rounded-t-2xl"></div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center text-red-500"><i class="fa-regular fa-clock text-base"></i></div>
                <span class="font-mono text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{{ $unpaidCount }} bills</span>
            </div>
            <div class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">Unpaid Bills</div>
            <div class="font-mono text-[20px] font-bold text-slate-900 tracking-tight">{{ $unpaidCount }}</div>
            <div class="text-[11px] text-slate-400 mt-1">${{ number_format($unpaidAmount, 2) }} remaining</div>
            <div class="h-1 bg-slate-100 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-red-600 rounded-full" style="width:{{ ($paidCount + $unpaidCount) > 0 ? ($unpaidCount/($paidCount+$unpaidCount))*100 : 0 }}%"></div>
            </div>
        </a>

        {{-- Net --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-violet-600 to-violet-400 rounded-t-2xl"></div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center text-violet-500"><i class="fa-regular fa-chart-bar text-base"></i></div>
            </div>
            <div class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">Net This Month</div>
            <div class="font-mono text-[20px] font-bold tracking-tight {{ $netAmount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                {{ $netAmount >= 0 ? '+' : '' }}${{ number_format($netAmount, 2) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">income minus bills</div>
        </div>

        {{-- Billers --}}
        <a href="{{ route('billers') }}" class="group bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-teal-600 to-teal-400 rounded-t-2xl"></div>
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600"><i class="fa-regular fa-building text-base"></i></div>
                <span class="font-mono text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">total</span>
            </div>
            <div class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">Billers</div>
            <div class="font-mono text-[20px] font-bold text-slate-900 tracking-tight">{{ $billerCount }}</div>
            <div class="text-[11px] text-slate-400 mt-1">active billers</div>
        </a>
    </div>

    {{-- Upcoming Bills --}}
    @if(count($upcoming) > 0)
    <div class="mb-2 flex items-center justify-between">
        <h2 class="text-[14px] font-bold text-slate-900">Upcoming Bills <span class="text-slate-400 font-normal text-[13px] ml-1">next 7 days</span></h2>
        <a href="{{ route('bills') }}" class="text-[12px] text-slate-500 hover:text-slate-900 transition-colors">View all →</a>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Biller</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Category</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Due</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Amount</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($upcoming as $bill)
                    <tr onclick="window.location='{{ route('bills', ['selectedDate' => $bill->date->toDateString(), 'y' => $year, 'm' => $month]) }}'" class="hover:bg-slate-50/50 transition-colors cursor-pointer">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[12px] font-bold flex-shrink-0"
                                     style="background: hsl({{ crc32($bill->getBillerName()) % 360 }}, 70%, 92%); color: hsl({{ crc32($bill->getBillerName()) % 360 }}, 60%, 35%)">
                                    {{ strtoupper(substr($bill->getBillerName(), 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-[13.5px] font-semibold text-slate-900">{{ $bill->getBillerName() }}</div>
                                    <div class="text-[11.5px] text-slate-400">{{ $bill->date->format('d M Y') }} · {{ $bill->getFrequencyName() }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 hidden sm:table-cell">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-slate-100 text-slate-600 border border-slate-200">{{ $bill->getCategoryName() }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-[12.5px] text-slate-700 hidden sm:table-cell">{{ $bill->date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 font-mono text-[13px] font-medium text-slate-900">${{ number_format($bill->getAmount(), 2) }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-red-50 text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>Due
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm">
        <div class="text-3xl mb-2">🎉</div>
        <div class="text-[14px] font-semibold text-slate-700">No upcoming bills in the next 7 days</div>
        <div class="text-[13px] text-slate-400 mt-1">You're all caught up for now.</div>
    </div>
    @endif
</div>
