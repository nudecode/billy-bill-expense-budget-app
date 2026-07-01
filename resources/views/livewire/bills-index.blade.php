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

        <button wire:click="openAddBillModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition-colors">
            <i class="fa-solid fa-plus text-xs"></i> Add Bill
        </button>
    </div>

    {{-- ── SUMMARY CARDS ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-2xl p-3 sm:p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-t-2xl"></div>
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="hidden sm:flex w-8 h-8 rounded-lg bg-emerald-50 items-center justify-center text-emerald-600 flex-shrink-0"><i class="fa-regular fa-circle-check"></i></div>
                <div class="min-w-0">
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-gray-400">Paid</div>
                    <div class="font-mono text-[12px] sm:text-[17px] font-bold text-emerald-600 truncate">${{ number_format($paidTotal, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-2xl p-3 sm:p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-red-500 to-red-400 rounded-t-2xl"></div>
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="hidden sm:flex w-8 h-8 rounded-lg bg-red-50 items-center justify-center text-red-500 flex-shrink-0"><i class="fa-regular fa-clock"></i></div>
                <div class="min-w-0">
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-gray-400">Unpaid</div>
                    <div class="font-mono text-[12px] sm:text-[17px] font-bold text-red-600 truncate">${{ number_format($unpaidTotal, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-2xl p-3 sm:p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-amber-500 to-amber-400 rounded-t-2xl"></div>
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="hidden sm:flex w-8 h-8 rounded-lg bg-amber-50 items-center justify-center text-amber-500 flex-shrink-0"><i class="fa-regular fa-file-lines"></i></div>
                <div class="min-w-0">
                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-gray-400">Total</div>
                    <div class="font-mono text-[12px] sm:text-[17px] font-bold text-gray-900 truncate">${{ number_format($paidTotal + $unpaidTotal, 2) }}</div>
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
        <div @if($bill->isPaid) wire:click="viewPaymentDetails('recurring', {{ $bill->rule->id }}, '{{ $bill->date->toDateString() }}')" @endif
             class="flex items-center justify-between px-5 py-4 border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors group {{ $bill->isPaid ? 'cursor-pointer' : '' }}">
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
            <div class="flex items-end gap-3 flex-shrink-0">
                <div class="text-right">
                    <div class="font-mono text-[15px] font-bold text-slate-900 leading-none">${{ number_format($bill->getAmount(), 2) }}</div>
                    @if($bill->isPaid)
                        <span class="block text-[11px] font-semibold text-emerald-600 mt-1 leading-none">Paid</span>
                    @else
                        @php $isOverdue = $bill->date->toDateString() < $today; @endphp
                        <span class="block text-[11px] font-semibold mt-1 leading-none {{ $isOverdue ? 'text-red-500' : 'text-slate-400' }}">{{ $isOverdue ? 'Overdue' : 'Due' }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-1 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                    @if(!$bill->isPaid)
                    @php $isOverdue = $bill->date->toDateString() < $today; @endphp
                    <button wire:click="openPayModal('recurring', {{ $bill->rule->id }}, '{{ $bill->date->toDateString() }}')"
                            class="w-8 h-8 flex items-center justify-center rounded-lg border transition-all {{ $isOverdue ? 'border-red-200 bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600' : 'border-slate-200 bg-white text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200' }}"
                            title="Record Payment">
                        <i class="fa-solid fa-dollar-sign text-sm"></i>
                    </button>
                    @endif
                    @if(!$bill->isPaid)
                    <button wire:click="openEditOccurrence({{ $bill->rule->id }}, '{{ $bill->date->toDateString() }}')"
                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all"
                            title="Edit">
                        <i class="fa-regular fa-pen-to-square text-sm"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        @if($listOneOffBills->isEmpty())
        <div class="px-5 py-10 text-center">
            <i class="fa-regular fa-calendar-days text-3xl text-slate-300 mb-3"></i>
            <div class="text-[14px] font-semibold text-slate-600">No bills on this day</div>
            <div class="text-[13px] text-slate-400 mt-1">Tap + Add Bill to schedule one.</div>
        </div>
        @endif
        @endforelse
        @foreach($listOneOffBills as $bill)
        <div @if($bill->is_paid) wire:click="viewPaymentDetails('oneoff', {{ $bill->id }})" @endif
             class="flex items-center justify-between px-5 py-4 border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors group {{ $bill->is_paid ? 'cursor-pointer' : '' }}">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-[13px] font-bold flex-shrink-0"
                     style="background: hsl({{ crc32($bill->biller->name) % 360 }}, 70%, 92%); color: hsl({{ crc32($bill->biller->name) % 360 }}, 60%, 35%)">
                    {{ strtoupper(substr($bill->biller->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-[14px] font-semibold text-slate-900 truncate">{{ $bill->biller->name }}</div>
                    <div class="text-[11.5px] text-slate-400">{{ $bill->category->name }} · One-off</div>
                </div>
            </div>
            <div class="flex items-end gap-3 flex-shrink-0">
                <div class="text-right">
                    <div class="font-mono text-[15px] font-bold text-slate-900 leading-none">${{ number_format($bill->amount, 2) }}</div>
                    @if($bill->is_paid)
                        <span class="block text-[11px] font-semibold text-emerald-600 mt-1 leading-none">Paid</span>
                    @else
                        @php $isOverdue = $bill->due_date->toDateString() < $today; @endphp
                        <span class="block text-[11px] font-semibold mt-1 leading-none {{ $isOverdue ? 'text-red-500' : 'text-slate-400' }}">{{ $isOverdue ? 'Overdue' : 'Due' }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-1 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                    @if(!$bill->is_paid)
                    @php $isOverdue = $bill->due_date->toDateString() < $today; @endphp
                    <button wire:click="openPayModal('oneoff', {{ $bill->id }})"
                            class="w-8 h-8 flex items-center justify-center rounded-lg border transition-all {{ $isOverdue ? 'border-red-200 bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600' : 'border-slate-200 bg-white text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200' }}"
                            title="Record Payment">
                        <i class="fa-solid fa-dollar-sign text-sm"></i>
                    </button>
                    @endif
                    <button wire:click="openEditOneOff({{ $bill->id }})"
                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all"
                            title="Edit">
                        <i class="fa-regular fa-pen-to-square text-sm"></i>
                    </button>
                    <button wire:click="confirmDeleteOneOff({{ $bill->id }})"
                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all"
                            title="Delete">
                        <i class="fa-regular fa-trash-can text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
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
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Due Date</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Amount</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Status</th>
                        <th class="px-2 sm:px-5 py-3 w-24"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($listInstances as $bill)
                    <tr @if($bill->isPaid) wire:click="viewPaymentDetails('recurring', {{ $bill->rule->id }}, '{{ $bill->date->toDateString() }}')" @endif
                        class="hover:bg-slate-50/50 transition-colors group {{ $bill->isPaid ? 'cursor-pointer' : '' }}">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[12px] font-bold flex-shrink-0"
                                     style="background: hsl({{ crc32($bill->getBillerName()) % 360 }}, 70%, 92%); color: hsl({{ crc32($bill->getBillerName()) % 360 }}, 60%, 35%)">
                                    {{ strtoupper(substr($bill->getBillerName(), 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-[13.5px] font-semibold text-slate-900">{{ $bill->getBillerName() }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $bill->date->format('d M Y') }} · {{ $bill->getFrequencyName() }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 hidden md:table-cell">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-slate-100 text-slate-600 border border-slate-200">{{ $bill->getCategoryName() }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-[12.5px] text-slate-700 hidden sm:table-cell">{{ $bill->date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="font-mono text-[13px] font-medium text-slate-900">${{ number_format($bill->getAmount(), 2) }}</div>
                            @if($bill->isPaid)
                                <span class="block sm:hidden text-[11px] font-semibold text-emerald-600 mt-0.5">Paid</span>
                            @elseif($bill->date->toDateString() < $today)
                                <span class="block sm:hidden text-[11px] font-semibold text-red-500 mt-0.5">Overdue</span>
                            @else
                                <span class="block sm:hidden text-[11px] font-semibold text-slate-400 mt-0.5">Due</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 hidden sm:table-cell">
                            @if($bill->isPaid)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>Paid</span>
                            @elseif($bill->date->toDateString() < $today)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>Overdue</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-slate-100 text-slate-500"><span class="w-1.5 h-1.5 rounded-full bg-slate-400 flex-shrink-0"></span>Due</span>
                            @endif
                        </td>
                        <td class="px-2 sm:px-5 py-3.5">
                            <div class="flex items-center gap-1 justify-end lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                @if(!$bill->isPaid)
                                @php $isOverdue = $bill->date->toDateString() < $today; @endphp
                                <button wire:click="openPayModal('recurring', {{ $bill->rule->id }}, '{{ $bill->date->toDateString() }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border transition-all {{ $isOverdue ? 'border-red-200 bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600' : 'border-slate-200 bg-white text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200' }}"
                                        title="Record Payment">
                                    <i class="fa-solid fa-dollar-sign text-sm"></i>
                                </button>
                                @endif
                                @if(!$bill->isPaid)
                                <button wire:click="openEditOccurrence({{ $bill->rule->id }}, '{{ $bill->date->toDateString() }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all"
                                        title="Edit">
                                    <i class="fa-regular fa-pen-to-square text-sm"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    @if($listOneOffBills->isEmpty())
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400 text-[13px]">No bills found for this period.</td></tr>
                    @endif
                    @endforelse
                    @foreach($listOneOffBills as $bill)
                    <tr @if($bill->is_paid) wire:click="viewPaymentDetails('oneoff', {{ $bill->id }})" @endif
                        class="hover:bg-slate-50/50 transition-colors group {{ $bill->is_paid ? 'cursor-pointer' : '' }}">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[12px] font-bold flex-shrink-0"
                                     style="background: hsl({{ crc32($bill->biller->name) % 360 }}, 70%, 92%); color: hsl({{ crc32($bill->biller->name) % 360 }}, 60%, 35%)">
                                    {{ strtoupper(substr($bill->biller->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-[13.5px] font-semibold text-slate-900">{{ $bill->biller->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $bill->due_date->format('d M Y') }} · One-off</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 hidden md:table-cell">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-slate-100 text-slate-600 border border-slate-200">{{ $bill->category->name }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-[12.5px] text-slate-700 hidden sm:table-cell">{{ $bill->due_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="font-mono text-[13px] font-medium text-slate-900">${{ number_format($bill->amount, 2) }}</div>
                            @if($bill->is_paid)
                                <span class="block sm:hidden text-[11px] font-semibold text-emerald-600 mt-0.5">Paid</span>
                            @elseif($bill->due_date->toDateString() < $today)
                                <span class="block sm:hidden text-[11px] font-semibold text-red-500 mt-0.5">Overdue</span>
                            @else
                                <span class="block sm:hidden text-[11px] font-semibold text-slate-400 mt-0.5">Due</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 hidden sm:table-cell">
                            @if($bill->is_paid)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>Paid</span>
                            @elseif($bill->due_date->toDateString() < $today)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>Overdue</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-slate-100 text-slate-500"><span class="w-1.5 h-1.5 rounded-full bg-slate-400 flex-shrink-0"></span>Due</span>
                            @endif
                        </td>
                        <td class="px-2 sm:px-5 py-3.5">
                            <div class="flex items-center gap-1 justify-end lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                @if(!$bill->is_paid)
                                @php $isOverdue = $bill->due_date->toDateString() < $today; @endphp
                                <button wire:click="openPayModal('oneoff', {{ $bill->id }})"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border transition-all {{ $isOverdue ? 'border-red-200 bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600' : 'border-slate-200 bg-white text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200' }}"
                                        title="Record Payment">
                                    <i class="fa-solid fa-dollar-sign text-sm"></i>
                                </button>
                                @endif
                                <button wire:click="openEditOneOff({{ $bill->id }})"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all"
                                        title="Edit">
                                    <i class="fa-regular fa-pen-to-square text-sm"></i>
                                </button>
                                <button wire:click="confirmDeleteOneOff({{ $bill->id }})"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all"
                                        title="Delete">
                                    <i class="fa-regular fa-trash-can text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── PAYMENT DETAIL MODAL ────────────────────────────────────────── --}}
    @if($showPaymentDetailModal && $viewingPaymentDetail)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.closePaymentDetailModal()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.closePaymentDetailModal()"></div>

        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            @php
                $paidDate = $viewingDetailType === 'oneoff' ? $viewingPaymentDetail->date_paid : $viewingPaymentDetail->payment_date;
                $dueDate = $viewingDetailType === 'oneoff' ? $viewingPaymentDetail->due_date : $viewingPaymentDetail->recurring_bill_date;
            @endphp

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-[13px] font-bold flex-shrink-0"
                         style="background: hsl({{ crc32($viewingPaymentDetail->biller->name) % 360 }}, 70%, 92%); color: hsl({{ crc32($viewingPaymentDetail->biller->name) % 360 }}, 60%, 35%)">
                        {{ strtoupper(substr($viewingPaymentDetail->biller->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-[15px] font-bold text-slate-900">{{ $viewingPaymentDetail->biller->name }}</div>
                        <div class="text-[11.5px] text-slate-400">{{ $viewingPaymentDetail->category->name }}</div>
                    </div>
                </div>
                <button wire:click="closePaymentDetailModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Amount banner --}}
            <div class="px-5 py-4 bg-emerald-50 border-b border-emerald-100 flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-widest text-emerald-600">Amount Paid</span>
                <span class="font-mono text-[22px] font-bold text-emerald-700">${{ number_format($viewingPaymentDetail->amount, 2) }}</span>
            </div>

            {{-- Details --}}
            <div class="px-5 py-4 space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-slate-50">
                    <span class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide">Paid On</span>
                    <span class="font-mono text-[13px] text-slate-800">{{ $paidDate->format('d M Y') }}</span>
                </div>
                @if($dueDate && $dueDate->toDateString() !== $paidDate->toDateString())
                <div class="flex items-center justify-between py-2 border-b border-slate-50">
                    <span class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide">Bill Due Date</span>
                    <span class="font-mono text-[13px] text-slate-800">{{ $dueDate->format('d M Y') }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between py-2 border-b border-slate-50">
                    <span class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide">Account</span>
                    <span class="text-[13px] text-slate-800">{{ $viewingPaymentDetail->account->name }}</span>
                </div>
                @if($viewingDetailType === 'recurring' && $viewingPaymentDetail->reference_number)
                <div class="flex items-center justify-between py-2 border-b border-slate-50">
                    <span class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide">Reference No.</span>
                    <span class="font-mono text-[13px] text-slate-800">{{ $viewingPaymentDetail->reference_number }}</span>
                </div>
                @endif
                @if($viewingDetailType === 'recurring' && $viewingPaymentDetail->notes)
                <div class="py-2">
                    <span class="text-[11.5px] font-semibold text-slate-400 uppercase tracking-wide block mb-1.5">Notes</span>
                    <p class="text-[13px] text-slate-700 leading-relaxed">{{ $viewingPaymentDetail->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-slate-100">
                <button wire:click="closePaymentDetailModal()" type="button"
                        class="w-full px-4 py-2.5 text-[13px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── ADD/EDIT BILL MODAL ─────────────────────────────────────────── --}}
    @if($showBillModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.closeBillModal()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.closeBillModal()"></div>

        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl z-10 max-h-[90vh] overflow-y-auto"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                        <i class="fa-solid fa-plus text-green-600 text-sm"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-slate-900">{{ $editingOneOffId ? 'Edit Bill' : 'Add Bill' }}</h2>
                </div>
                <button wire:click="closeBillModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-5 py-4 space-y-4">

                {{-- Biller autocomplete + quick-add --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Biller</label>
                    <div class="flex gap-2">
                        <div class="relative flex-1"
                             x-data="{
                                open: false,
                                search: @js($billerSearch),
                                billers: @js($billers->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])->values()),
                                get filtered() {
                                    const q = (this.search || '').toLowerCase();
                                    if (!q) return this.billers;
                                    return this.billers.filter(b => b.name.toLowerCase().includes(q));
                                },
                                init() {
                                    Livewire.on('biller-quick-added', ({ id, name }) => {
                                        this.billers.push({ id, name });
                                        this.search = name;
                                    });
                                }
                             }">
                            <input type="text" x-model="search" @focus="open = true" @click.away="open = false"
                                   placeholder="Search billers…" autocomplete="off"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('billBillerId') border-red-400 @enderror">
                            <div x-show="open && filtered.length > 0" x-cloak
                                 class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="b in filtered" :key="b.id">
                                    <button type="button" @click="$wire.selectBiller(b.id); search = b.name; open = false"
                                            class="w-full text-left px-3 py-2 text-[13px] text-slate-700 hover:bg-slate-50 transition-colors" x-text="b.name"></button>
                                </template>
                            </div>
                        </div>
                        <button type="button" wire:click="openQuickAddBiller()" title="Add a new biller"
                                class="w-[38px] h-[38px] flex-shrink-0 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-green-50 hover:text-green-600 hover:border-green-200 transition-all">
                            <i class="fa-solid fa-plus text-sm"></i>
                        </button>
                    </div>
                    @error('billBillerId') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Amount</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono text-[13px]">$</span>
                            <input wire:model="billAmount" type="number" step="0.01" min="0.01"
                                   class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg font-mono text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('billAmount') border-red-400 @enderror">
                        </div>
                        @error('billAmount') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Date</label>
                        <div class="flex gap-2">
                            @if($isRecurring)
                            <input wire:model="billStartDate" type="date"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('billStartDate') border-red-400 @enderror">
                            @else
                            <input wire:model="billDueDate" type="date"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('billDueDate') border-red-400 @enderror">
                            @endif
                            @if(!$editingOneOffId)
                            <button type="button" wire:click="toggleRecurring()" title="{{ $isRecurring ? 'Recurring bill' : 'Make this a recurring bill' }}"
                                    class="w-[38px] h-[38px] flex-shrink-0 flex items-center justify-center rounded-lg border transition-all {{ $isRecurring ? 'bg-green-600 border-green-600 text-white' : 'border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-600' }}">
                                <i class="fa-solid fa-rotate text-sm"></i>
                            </button>
                            @endif
                        </div>
                        @error('billStartDate') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                        @error('billDueDate') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Category</label>
                        <select wire:model.live="billCategoryId"
                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] bg-white focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('billCategoryId') border-red-400 @enderror">
                            <option value="">Select…</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('billCategoryId') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    @if($isRecurring)
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Frequency</label>
                        <select wire:model="billFrequencyId"
                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] bg-white focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('billFrequencyId') border-red-400 @enderror">
                            <option value="">Select…</option>
                            @foreach($frequencies as $freq)
                                <option value="{{ $freq->id }}">{{ $freq->name }}</option>
                            @endforeach
                        </select>
                        @error('billFrequencyId') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    @else
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Subcategory <span class="font-normal normal-case tracking-normal text-slate-300">(optional)</span></label>
                        <select wire:model="billSubcategoryId" wire:key="subcat-{{ $billCategoryId }}"
                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] bg-white focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('billSubcategoryId') border-red-400 @enderror">
                            <option value="">None</option>
                            @foreach($subcategories->where('category_id', $billCategoryId) as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                        @error('billSubcategoryId') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    @endif
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Account</label>
                    <select wire:model="billAccountId"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] bg-white focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('billAccountId') border-red-400 @enderror">
                        <option value="">Select…</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('billAccountId') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                @if($isRecurring)
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">End Date</label>
                    <input wire:model="billEndDate" type="date"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('billEndDate') border-red-400 @enderror">
                    <p class="text-[11.5px] text-slate-400 mt-1">Leave blank for a bill with no end date</p>
                    <div class="flex gap-1.5 mt-2">
                        <button type="button" wire:click="setBillEndDateOffset('3m')" class="px-2.5 py-1 text-[11.5px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition-colors">3 mo</button>
                        <button type="button" wire:click="setBillEndDateOffset('6m')" class="px-2.5 py-1 text-[11.5px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition-colors">6 mo</button>
                        <button type="button" wire:click="setBillEndDateOffset('1y')" class="px-2.5 py-1 text-[11.5px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition-colors">1 yr</button>
                        <button type="button" wire:click="setBillEndDateOffset('2y')" class="px-2.5 py-1 text-[11.5px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition-colors">2 yr</button>
                    </div>
                    @error('billEndDate') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                @if($editingOneOffId)
                <button wire:click="switchToDeleteOneOff()" type="button"
                        class="text-[12.5px] text-red-500 hover:text-red-600 font-semibold transition-colors">
                    <i class="fa-regular fa-trash-can mr-1"></i>Delete this bill instead
                </button>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                <button wire:click="closeBillModal()" type="button"
                        class="px-4 py-2 text-[13px] font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-all">
                    Cancel
                </button>
                <button wire:click="saveBill()" wire:loading.attr="disabled" wire:target="saveBill"
                        class="flex items-center gap-2 px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-[13px] font-semibold rounded-lg transition-all disabled:opacity-60">
                    <span wire:loading.remove wire:target="saveBill">Save</span>
                    <span wire:loading wire:target="saveBill" class="flex items-center gap-1.5"><i class="fa-solid fa-spinner fa-spin text-xs"></i> Saving…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── QUICK ADD BILLER MODAL (stacked above Add/Edit Bill modal) ────── --}}
    @if($showQuickAddBillerModal)
    <div class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.closeQuickAddBiller()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.closeQuickAddBiller()"></div>

        <div class="relative bg-white w-full sm:max-w-sm rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                        <i class="fa-solid fa-plus text-green-600 text-sm"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-slate-900">Add Biller</h2>
                </div>
                <button wire:click="closeQuickAddBiller()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-5 py-4">
                <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Name</label>
                <input wire:model="quickBillerName" type="text" placeholder="e.g. Origin Energy" autofocus
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('quickBillerName') border-red-400 @enderror">
                @error('quickBillerName') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-[11.5px] text-slate-400 mt-2">Add phone, email, or account number later from the Billers page.</p>
            </div>

            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                <button wire:click="closeQuickAddBiller()" type="button"
                        class="px-4 py-2 text-[13px] font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-all">
                    Cancel
                </button>
                <button wire:click="saveQuickAddBiller()" wire:loading.attr="disabled" wire:target="saveQuickAddBiller"
                        class="flex items-center gap-2 px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-[13px] font-semibold rounded-lg transition-all disabled:opacity-60">
                    <span wire:loading.remove wire:target="saveQuickAddBiller">Save</span>
                    <span wire:loading wire:target="saveQuickAddBiller" class="flex items-center gap-1.5"><i class="fa-solid fa-spinner fa-spin text-xs"></i> Saving…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── DELETE ONE-OFF BILL MODAL ───────────────────────────────────── --}}
    @if($showDeleteOneOffModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.cancelDeleteOneOff()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.cancelDeleteOneOff()"></div>

        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <i class="fa-regular fa-trash-can text-red-500 text-sm"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-slate-900">Delete Bill</h2>
                </div>
                <button wire:click="cancelDeleteOneOff()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-5 py-4">
                <p class="text-[13.5px] text-slate-600 leading-relaxed">
                    Are you sure you want to delete this bill? This cannot be undone.
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                <button wire:click="cancelDeleteOneOff()" type="button"
                        class="px-4 py-2 text-[13px] font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-all">
                    Cancel
                </button>
                <button wire:click="deleteOneOff()" wire:loading.attr="disabled" wire:target="deleteOneOff"
                        class="flex items-center gap-2 px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-[13px] font-semibold rounded-lg transition-all disabled:opacity-60">
                    <span wire:loading.remove wire:target="deleteOneOff">Delete</span>
                    <span wire:loading wire:target="deleteOneOff" class="flex items-center gap-1.5"><i class="fa-solid fa-spinner fa-spin text-xs"></i> Deleting…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── RECURRING PROMPT MODAL ──────────────────────────────────────── --}}
    @if($showRecurringPromptModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.dismissRecurringPrompt()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.dismissRecurringPrompt()"></div>

        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                        <i class="fa-regular fa-lightbulb text-amber-500 text-sm"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-slate-900">Set Up Recurring Bill?</h2>
                </div>
                <button wire:click="dismissRecurringPrompt()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-5 py-4">
                <p class="text-[13.5px] text-slate-600 leading-relaxed">
                    You've added <span class="font-semibold text-slate-900">{{ $promptBillerName }}</span> manually twice. Set up a recurring bill so it's tracked automatically each period?
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                <button wire:click="dismissRecurringPrompt()" type="button"
                        class="px-4 py-2 text-[13px] font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-all">
                    No thanks
                </button>
                <button wire:click="acceptRecurringPrompt()"
                        class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-[13px] font-semibold rounded-lg transition-all">
                    Set up recurring bill
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── PAY MODAL ────────────────────────────────────────────────── --}}
    @if($showPayModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.closePayModal()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.closePayModal()"></div>

        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-slate-900">Record Payment</h2>
                </div>
                <button wire:click="closePayModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-5 py-4 space-y-4">

                {{-- Date --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Payment Date</label>
                    <input wire:model="payDate" type="date"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('payDate') border-red-400 @enderror">
                    @error('payDate') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Amount --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono text-[13px]">$</span>
                        <input wire:model="payAmount" type="number" step="0.01" min="0"
                               class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg font-mono text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('payAmount') border-red-400 @enderror">
                    </div>
                    @error('payAmount') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                @if($payType === 'recurring')
                {{-- Reference number --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Reference / Receipt No. <span class="font-normal normal-case tracking-normal text-slate-300">(optional)</span></label>
                    <input wire:model="payReference" type="text" placeholder="e.g. ABC123456"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('payReference') border-red-400 @enderror">
                    @error('payReference') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Notes <span class="font-normal normal-case tracking-normal text-slate-300">(optional)</span></label>
                    <textarea wire:model="payNotes" rows="2" placeholder="Any notes about this payment…"
                              class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] resize-none focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('payNotes') border-red-400 @enderror"></textarea>
                    @error('payNotes') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                <button wire:click="closePayModal()" type="button"
                        class="px-4 py-2 text-[13px] font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-all">
                    Cancel
                </button>
                <button wire:click="savePayment()" wire:loading.attr="disabled" wire:target="savePayment"
                        class="flex items-center gap-2 px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-[13px] font-semibold rounded-lg transition-all disabled:opacity-60">
                    <span wire:loading.remove wire:target="savePayment"><i class="fa-solid fa-circle-check mr-1"></i>Save Payment</span>
                    <span wire:loading wire:target="savePayment" class="flex items-center gap-1.5"><i class="fa-solid fa-spinner fa-spin text-xs"></i> Saving…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── EDIT OCCURRENCE MODAL ───────────────────────────────────────── --}}
    @if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.closeEditModal()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.closeEditModal()"></div>

        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                        <i class="fa-regular fa-pen-to-square text-slate-500 text-sm"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-slate-900">Edit Bill — {{ \Carbon\Carbon::parse($editingDate)->format('d M Y') }}</h2>
                </div>
                <button wire:click="closeEditModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-5 py-4 space-y-4">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono text-[13px]">$</span>
                        <input wire:model="editAmount" type="number" step="0.01" min="0.01"
                               class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg font-mono text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('editAmount') border-red-400 @enderror">
                    </div>
                    @error('editAmount') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Category</label>
                        <select wire:model="editCategoryId"
                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] bg-white focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('editCategoryId') border-red-400 @enderror">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('editCategoryId') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Account</label>
                        <select wire:model="editAccountId"
                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] bg-white focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('editAccountId') border-red-400 @enderror">
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('editAccountId') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button wire:click="switchToDeleteOccurrence()" type="button"
                        class="text-[12.5px] text-red-500 hover:text-red-600 font-semibold transition-colors">
                    <i class="fa-regular fa-trash-can mr-1"></i>Remove this bill instead
                </button>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                <button wire:click="closeEditModal()" type="button"
                        class="px-4 py-2 text-[13px] font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-all">
                    Cancel
                </button>
                <button wire:click="proceedFromEdit()" wire:loading.attr="disabled" wire:target="proceedFromEdit"
                        class="flex items-center gap-2 px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-[13px] font-semibold rounded-lg transition-all disabled:opacity-60">
                    <span wire:loading.remove wire:target="proceedFromEdit">Save</span>
                    <span wire:loading wire:target="proceedFromEdit" class="flex items-center gap-1.5"><i class="fa-solid fa-spinner fa-spin text-xs"></i> Saving…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── EDIT SCOPE MODAL ────────────────────────────────────────────── --}}
    @if($showEditScopeModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.cancelEditScope()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.cancelEditScope()"></div>

        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                        <i class="fa-regular fa-pen-to-square text-slate-500 text-sm"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-slate-900">Apply Changes To</h2>
                </div>
                <button wire:click="cancelEditScope()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-5 py-4">
                <p class="text-[13.5px] text-slate-600 leading-relaxed mb-3">
                    Updating just this occurrence leaves every other month of this recurring bill untouched.
                </p>
                <button wire:click="saveEditOccurrence('this')" wire:loading.attr="disabled" wire:target="saveEditOccurrence"
                        class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all disabled:opacity-60">
                    <span class="text-[13px] font-semibold text-slate-800">This occurrence only</span>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </button>
            </div>

            <div class="px-5 py-4 border-t border-slate-100">
                <p class="text-[13.5px] text-slate-600 leading-relaxed mb-3">
                    Updating this and all future occurrences changes the recurring bill permanently from this date onward.
                </p>
                <button wire:click="saveEditOccurrence('future')" wire:loading.attr="disabled" wire:target="saveEditOccurrence"
                        class="w-full flex items-center justify-between px-4 py-2.5 bg-green-600 hover:bg-green-700 rounded-lg transition-all disabled:opacity-60">
                    <span class="text-[13px] font-semibold text-white">This and all future occurrences</span>
                    <i class="fa-solid fa-chevron-right text-white/70 text-xs"></i>
                </button>
            </div>

            <div class="px-5 py-4 border-t border-slate-100">
                <button wire:click="cancelEditScope()" class="w-full px-4 py-2.5 text-[13px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── DELETE OCCURRENCE MODAL ─────────────────────────────────────── --}}
    @if($showDeleteOccurrenceModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.cancelDeleteOccurrence()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.cancelDeleteOccurrence()"></div>

        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <i class="fa-regular fa-trash-can text-red-500 text-sm"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-slate-900">Remove Bill</h2>
                </div>
                <button wire:click="cancelDeleteOccurrence()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-5 py-4">
                <p class="text-[13.5px] text-slate-600 leading-relaxed mb-3">
                    Removing just this occurrence leaves the recurring bill and every other month untouched.
                </p>
                <button wire:click="deleteOccurrenceThisOnly()" wire:loading.attr="disabled" wire:target="deleteOccurrenceThisOnly"
                        class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all disabled:opacity-60">
                    <span class="text-[13px] font-semibold text-slate-800">This occurrence only</span>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </button>
            </div>

            <div class="px-5 py-4 border-t border-slate-100">
                <p class="text-[13.5px] text-slate-600 leading-relaxed mb-3">
                    Removing this and all future occurrences ends the recurring bill from this date onward.
                </p>
                <button wire:click="deleteOccurrenceAllFuture()" wire:loading.attr="disabled" wire:target="deleteOccurrenceAllFuture"
                        class="w-full flex items-center justify-between px-4 py-2.5 bg-red-600 hover:bg-red-700 rounded-lg transition-all disabled:opacity-60">
                    <span class="text-[13px] font-semibold text-white">This and all future occurrences</span>
                    <i class="fa-solid fa-chevron-right text-white/70 text-xs"></i>
                </button>
            </div>

            <div class="px-5 py-4 border-t border-slate-100">
                <button wire:click="cancelDeleteOccurrence()" class="w-full px-4 py-2.5 text-[13px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
