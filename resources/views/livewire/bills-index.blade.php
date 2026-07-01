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
                    <button wire:click="openPayModal({{ $bill->rule->id }}, '{{ $bill->date->toDateString() }}')"
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
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Due Date</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Amount</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Status</th>
                        <th class="px-2 sm:px-5 py-3 w-24"></th>
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
                                <button wire:click="openPayModal({{ $bill->rule->id }}, '{{ $bill->date->toDateString() }}')"
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
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400 text-[13px]">No bills found for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
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

            {{-- Footer — apply-to choice --}}
            <div class="px-5 py-4 border-t border-slate-100 space-y-2">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Apply changes to</p>
                <button wire:click="saveEditOccurrence('this')" wire:loading.attr="disabled" wire:target="saveEditOccurrence"
                        class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all disabled:opacity-60">
                    <span class="text-[13px] font-semibold text-slate-800">This occurrence only</span>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </button>
                <button wire:click="saveEditOccurrence('future')" wire:loading.attr="disabled" wire:target="saveEditOccurrence"
                        class="w-full flex items-center justify-between px-4 py-2.5 bg-green-600 hover:bg-green-700 rounded-lg transition-all disabled:opacity-60">
                    <span class="text-[13px] font-semibold text-white">This and all future occurrences</span>
                    <i class="fa-solid fa-chevron-right text-white/70 text-xs"></i>
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

            <div class="px-5 py-4 space-y-2">
                <p class="text-[13.5px] text-slate-600 leading-relaxed mb-2">
                    Removing just this occurrence leaves the recurring bill and every other month untouched.
                    Removing this and all future occurrences ends the recurring bill from this date onward.
                </p>
                <button wire:click="deleteOccurrenceThisOnly()" wire:loading.attr="disabled" wire:target="deleteOccurrenceThisOnly"
                        class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all disabled:opacity-60">
                    <span class="text-[13px] font-semibold text-slate-800">This occurrence only</span>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </button>
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
