<div>
    {{-- ── TOP BAR ────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">

        {{-- Month navigator --}}
        <div class="flex items-center gap-2">
            <div class="flex items-center bg-slate-100 border border-slate-200 rounded-full overflow-hidden">
                <button wire:click="{{ $calView === 'week' ? 'previousWeek' : 'previousMonth' }}"
                        class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <span class="font-mono text-[12px] font-medium text-slate-800 px-3 min-w-[110px] text-center">{{ strtoupper($periodLabel) }}</span>
                <button wire:click="{{ $calView === 'week' ? 'nextWeek' : 'nextMonth' }}"
                        class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            {{-- Week / Month toggle --}}
            <div class="flex gap-0.5 p-0.5 bg-slate-100 rounded-lg border border-slate-200">
                <button wire:click="$set('calView','week')"
                        class="px-3 py-1 rounded-md text-[12px] font-semibold transition-all {{ $calView === 'week' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    Week
                </button>
                <button wire:click="$set('calView','month')"
                        class="px-3 py-1 rounded-md text-[12px] font-semibold transition-all {{ $calView === 'month' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    Month
                </button>
            </div>
        </div>

        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition-colors">
            <i class="fa-solid fa-plus text-xs"></i> Add Bill
        </button>
    </div>

    {{-- ── SUMMARY CARDS ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-t-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0"><i class="fa-regular fa-circle-check"></i></div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Paid</div>
                    <div class="font-mono text-[13px] sm:text-[17px] font-bold text-emerald-600">${{ number_format($paidTotal, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-red-500 to-red-400 rounded-t-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-500 flex-shrink-0"><i class="fa-regular fa-clock"></i></div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Unpaid</div>
                    <div class="font-mono text-[13px] sm:text-[17px] font-bold text-red-600">${{ number_format($unpaidTotal, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-amber-500 to-amber-400 rounded-t-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500 flex-shrink-0"><i class="fa-regular fa-file-lines"></i></div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Total</div>
                    <div class="font-mono text-[13px] sm:text-[17px] font-bold text-gray-900">${{ number_format($paidTotal + $unpaidTotal, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── CALENDAR ─────────────────────────────────────────────────── --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm mb-5 overflow-hidden">

        @php
        $currentMonthPrefix = sprintf('%04d-%02d', $year, $month);

        /**
         * Returns the CSS classes for a calendar day cell.
         * Priority: selected > today > has bills (paid/unpaid) > plain
         */
        $dayClasses = function(string $date, string $selectedDate, string $today, array $dayStatus, string $currentMonthPrefix): string
        {
            $isCurrentMonth = str_starts_with($date, $currentMonthPrefix);
            $isSelected     = $date === $selectedDate;
            $isToday        = $date === $today;
            $status         = $dayStatus[$date] ?? null;

            if (!$isCurrentMonth) {
                return 'text-gray-300 cursor-default';
            }

            if ($isSelected) {
                return 'bg-green-600 text-white font-bold rounded-full cursor-pointer';
            }

            if ($status) {
                $ring = $status['all_paid']
                    ? 'ring-2 ring-green-500 text-slate-800'
                    : 'ring-2 ring-red-500 text-red-600';

                if ($isToday) {
                    return "bg-green-600 text-white font-bold rounded-full ring-0 cursor-pointer";
                }

                return "{$ring} rounded-full cursor-pointer hover:opacity-75 transition-opacity";
            }

            if ($isToday) {
                return 'bg-slate-800 text-white font-bold rounded-full cursor-pointer';
            }

            return 'text-slate-700 cursor-pointer hover:bg-gray-50 rounded-full transition-colors';
        }
        @endphp

        @if($calView === 'week')
        {{-- ── WEEK STRIP ──────────────────────────────────────────── --}}
        <div class="px-4 py-4">
            {{-- Day headers --}}
            <div class="grid grid-cols-7 mb-2">
                @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dayLabel)
                <div class="text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wide">{{ $dayLabel }}</div>
                @endforeach
            </div>
            {{-- Day cells --}}
            <div class="grid grid-cols-7 gap-1">
                @foreach($weekDays as $date)
                @php
                    $classes = $dayClasses($date, $selectedDate, $today, $dayStatus, $currentMonthPrefix);
                    $dayNum  = \Carbon\Carbon::parse($date)->day;
                    $isInteractive = str_starts_with($date, $currentMonthPrefix);
                @endphp
                <div class="flex justify-center">
                    <button
                        @if($isInteractive) wire:click="selectDate('{{ $date }}')" @endif
                        class="w-10 h-10 flex items-center justify-center text-[14px] {{ $classes }}"
                    >{{ $dayNum }}</button>
                </div>
                @endforeach
            </div>
        </div>

        @else
        {{-- ── MONTH GRID ──────────────────────────────────────────── --}}
        <div class="px-4 py-4">
            {{-- Day headers --}}
            <div class="grid grid-cols-7 mb-2">
                @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dayLabel)
                <div class="text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wide">{{ $dayLabel }}</div>
                @endforeach
            </div>
            {{-- Day cells --}}
            <div class="grid grid-cols-7 gap-y-1">
                @foreach($calDays as $date)
                @php
                    $classes = $dayClasses($date, $selectedDate, $today, $dayStatus, $currentMonthPrefix);
                    $dayNum  = \Carbon\Carbon::parse($date)->day;
                    $isInteractive = str_starts_with($date, $currentMonthPrefix);
                @endphp
                <div class="flex justify-center py-0.5">
                    <button
                        @if($isInteractive) wire:click="selectDate('{{ $date }}')" @endif
                        class="w-9 h-9 flex items-center justify-center text-[13px] {{ $classes }}"
                    >{{ $dayNum }}</button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- ── BILL LIST ────────────────────────────────────────────────── --}}

    @if($selectedDate)
    {{-- Filtered to a single day --}}
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-[14px] font-bold text-slate-900">
            {{ \Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }}
        </h2>
        <button wire:click="$set('selectedDate','')" class="text-[12px] text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-1">
            <i class="fa-solid fa-xmark text-[10px]"></i> Show all
        </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        @forelse($listInstances as $bill)
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors group">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-[13px] font-bold flex-shrink-0"
                     style="background: hsl({{ crc32($bill->getBillerName()) % 360 }}, 70%, 92%); color: hsl({{ crc32($bill->getBillerName()) % 360 }}, 60%, 35%)">
                    {{ strtoupper(substr($bill->getBillerName(), 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-[14px] font-semibold text-slate-900 truncate">{{ $bill->getBillerName() }}</div>
                    <div class="text-[11.5px] text-slate-400">{{ $bill->getCategoryName() }} · {{ $bill->getFrequencyName() }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <div class="text-right">
                    <div class="font-mono text-[15px] font-bold text-slate-900">${{ number_format($bill->getAmount(), 2) }}</div>
                    @if($bill->isPaid)
                        <span class="text-[11px] font-semibold text-emerald-600">Paid</span>
                    @else
                        <span class="text-[11px] font-semibold text-red-500">Due</span>
                    @endif
                </div>
                <div class="flex items-center gap-1 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                    @if(!$bill->isPaid)
                    <button wire:click="markPaid({{ $bill->rule->id }}, '{{ $bill->date->toDateString() }}')"
                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all text-sm"
                            title="Mark Paid">💳</button>
                    @endif
                    <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all text-sm">✏️</button>
                </div>
            </div>
        </div>
        @empty
        <div class="px-5 py-10 text-center">
            <i class="fa-regular fa-calendar-days text-3xl text-slate-300 mb-3"></i>
            <div class="text-[14px] font-semibold text-slate-600">No bills on this day</div>
            <div class="text-[13px] text-slate-400 mt-1">Tap + Add Bill to schedule one.</div>
        </div>
        @endforelse
    </div>

    @else
    {{-- Full month list with tabs and search --}}
    <div class="flex gap-1 p-1 bg-slate-100 rounded-xl w-fit mb-4">
        <button wire:click="$set('tab','all')" class="px-4 py-1.5 rounded-lg text-[13px] font-semibold transition-all {{ $tab === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">All</button>
        <button wire:click="$set('tab','unpaid')" class="px-4 py-1.5 rounded-lg text-[13px] font-semibold transition-all {{ $tab === 'unpaid' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">Unpaid</button>
        <button wire:click="$set('tab','paid')" class="px-4 py-1.5 rounded-lg text-[13px] font-semibold transition-all {{ $tab === 'paid' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">Paid</button>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200">
            <div class="relative w-52">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search bills…" class="pl-9 pr-4 py-1.5 border border-slate-200 rounded-lg text-[13px] bg-slate-50 focus:bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 outline-none transition-all w-full">
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
                    @forelse($listInstances as $bill)
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
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>Paid</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>Due</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1 justify-end lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                @if(!$bill->isPaid)
                                <button wire:click="markPaid({{ $bill->rule->id }}, '{{ $bill->date->toDateString() }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all text-sm"
                                        title="Mark Paid">💳</button>
                                @endif
                                <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all text-sm">✏️</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400 text-[13px]">No bills found for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
