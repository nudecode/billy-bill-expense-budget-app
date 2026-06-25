<div>
    {{-- ── TOP BAR ─────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="relative">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search…" class="pl-9 pr-4 py-1.5 border border-slate-200 rounded-lg text-[13px] bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 outline-none w-52">
        </div>
        <button wire:click="openCreate()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition-colors">
            <i class="fa-solid fa-plus text-xs"></i> Add Recurring Bill
        </button>
    </div>

    {{-- ── TABLE ───────────────────────────────────────────────────── --}}
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
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widests text-slate-400 hidden lg:table-cell">Status</th>
                        <th class="px-2 sm:px-5 py-3 w-24"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rules as $rule)
                    @php
                        $today    = now()->toDateString();
                        $isEnded  = $rule->end_date && $rule->end_date->toDateString() < $today;
                        $endingSoon = $rule->end_date && !$isEnded && $rule->end_date->diffInDays(now()) <= 30;
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors group {{ $isEnded ? 'opacity-60' : '' }}">
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
                        <td class="px-5 py-3.5 font-mono text-[12.5px] hidden md:table-cell {{ $endingSoon ? 'text-amber-500 font-semibold' : 'text-slate-400' }}">
                            {{ $rule->end_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 hidden lg:table-cell">
                            @if($isEnded)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-slate-100 text-slate-400"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 flex-shrink-0"></span>Ended</span>
                            @elseif($endingSoon)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-amber-50 text-amber-600"><span class="w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>Ending soon</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11.5px] font-semibold bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>Active</span>
                            @endif
                        </td>
                        <td class="px-2 sm:px-5 py-3.5">
                            <div class="flex items-center gap-1 justify-end lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                <button wire:click="openEdit({{ $rule->id }})"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all"
                                        title="Edit">
                                    <i class="fa-regular fa-pen-to-square text-sm"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $rule->id }})"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all"
                                        title="{{ $rule->payments()->exists() ? 'End bill' : 'Delete' }}">
                                    <i class="fa-regular fa-trash-can text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400 text-[13px]">No recurring bills yet. Add one to get started.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── CREATE / EDIT MODAL ─────────────────────────────────────── --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.closeModal()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.closeModal()"></div>

        <div class="relative bg-white w-full sm:max-w-lg rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                        <i class="fa-solid fa-repeat text-amber-500 text-sm"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-slate-900">{{ $editingId ? 'Edit Recurring Bill' : 'Add Recurring Bill' }}</h2>
                </div>
                <button wire:click="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-5 py-4 space-y-4 max-h-[70vh] overflow-y-auto">

                {{-- Biller --}}
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Biller</label>
                    <select wire:model="billerId"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] bg-white focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('billerId') border-red-400 @enderror">
                        <option value="">Select a biller…</option>
                        @foreach($billers as $biller)
                            <option value="{{ $biller->id }}">{{ $biller->name }}</option>
                        @endforeach
                    </select>
                    @error('billerId') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Amount + Frequency --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Amount</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono text-[13px]">$</span>
                            <input wire:model="amount" type="number" step="0.01" min="0.01" placeholder="0.00"
                                   class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg font-mono text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('amount') border-red-400 @enderror">
                        </div>
                        @error('amount') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Frequency</label>
                        <select wire:model="frequencyId"
                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] bg-white focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('frequencyId') border-red-400 @enderror">
                            <option value="">Select…</option>
                            @foreach($frequencies as $freq)
                                <option value="{{ $freq->id }}">{{ $freq->name }}</option>
                            @endforeach
                        </select>
                        @error('frequencyId') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Category + Account --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widests text-slate-400 mb-1.5">Category</label>
                        <select wire:model="categoryId"
                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] bg-white focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('categoryId') border-red-400 @enderror">
                            <option value="">Select…</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('categoryId') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Account</label>
                        <select wire:model="accountId"
                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] bg-white focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('accountId') border-red-400 @enderror">
                            <option value="">Select…</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('accountId') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Start Date + End Date --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Start Date</label>
                        <input wire:model="startDate" type="date"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('startDate') border-red-400 @enderror">
                        @error('startDate') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">
                            End Date <span class="font-normal normal-case tracking-normal text-slate-300">(optional)</span>
                        </label>
                        <input wire:model="endDate" type="date"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('endDate') border-red-400 @enderror">
                        @error('endDate') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                <button wire:click="closeModal()" type="button"
                        class="px-4 py-2 text-[13px] font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-all">
                    Cancel
                </button>
                <button wire:click="save()" wire:loading.attr="disabled" wire:target="save"
                        class="flex items-center gap-2 px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-[13px] font-semibold rounded-lg transition-all disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Create Bill' }}</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-1.5"><i class="fa-solid fa-spinner fa-spin text-xs"></i> Saving…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── DELETE / END MODAL ──────────────────────────────────────── --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.cancelDelete()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             @click="$wire.cancelDelete()"></div>

        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            @if($hasPayments)
            {{-- Has payment history — offer End options only --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                        <i class="fa-solid fa-calendar-xmark text-amber-500 text-sm"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-slate-900">End Recurring Bill</h2>
                </div>
                <button wire:click="cancelDelete()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="px-5 py-4 space-y-4">
                <p class="text-[13.5px] text-slate-600 leading-relaxed">
                    This bill has payment history, so it can't be deleted. You can end it — no new occurrences will be generated after the end date, and all existing payments are preserved.
                </p>
                <div class="flex gap-3">
                    <button wire:click="endToday()"
                            class="flex-1 px-4 py-2.5 bg-amber-50 border border-amber-200 text-amber-700 text-[13px] font-semibold rounded-lg hover:bg-amber-100 transition-all">
                        <i class="fa-solid fa-calendar-check mr-1.5"></i>End Today
                    </button>
                </div>
                <div class="border-t border-slate-100 pt-4">
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Or end on a specific date</label>
                    <div class="flex gap-2">
                        <input wire:model="endDateChoice" type="date"
                               class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-[13.5px] focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all">
                        <button wire:click="endOnDate()"
                                class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-[13px] font-semibold rounded-lg hover:bg-slate-50 transition-all">
                            Set Date
                        </button>
                    </div>
                    @error('endDateChoice') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="px-5 py-4 border-t border-slate-100">
                <button wire:click="cancelDelete()" class="w-full px-4 py-2.5 text-[13px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-all">
                    Cancel
                </button>
            </div>

            @else
            {{-- No payment history — offer full delete --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <i class="fa-regular fa-trash-can text-red-500 text-sm"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-slate-900">Delete Recurring Bill</h2>
                </div>
                <button wire:click="cancelDelete()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="px-5 py-4">
                <p class="text-[13.5px] text-slate-600 leading-relaxed">
                    This bill has no payment history, so it can be fully deleted. This action cannot be undone.
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                <button wire:click="cancelDelete()" type="button"
                        class="px-4 py-2 text-[13px] font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-all">
                    Cancel
                </button>
                <button wire:click="deleteRule()" wire:loading.attr="disabled" wire:target="deleteRule"
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-[13px] font-semibold rounded-lg transition-all disabled:opacity-60">
                    <span wire:loading.remove wire:target="deleteRule"><i class="fa-regular fa-trash-can mr-1"></i>Delete</span>
                    <span wire:loading wire:target="deleteRule"><i class="fa-solid fa-spinner fa-spin text-xs"></i> Deleting…</span>
                </button>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
