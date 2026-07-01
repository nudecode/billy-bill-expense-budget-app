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
                        <th class="px-2 sm:px-5 py-3 w-24"></th>
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
                        <td class="px-2 sm:px-5 py-3.5">
                            <div class="flex items-center gap-1 justify-end lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                <button wire:click="openEditOccurrence({{ $income->rule->id }}, '{{ $income->date->toDateString() }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all"
                                        title="Edit">
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
                        <td class="px-2 sm:px-5 py-3.5">
                            <div class="flex items-center gap-1 justify-end lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                <button disabled title="Editing individual occurrences is coming soon"
                                        class="hidden sm:flex w-8 h-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-300 cursor-not-allowed">
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
                    <h2 class="text-[15px] font-bold text-slate-900">Edit Income — {{ \Carbon\Carbon::parse($editingDate)->format('d M Y') }}</h2>
                </div>
                <button wire:click="closeEditModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-5 py-4 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Amount</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono text-[13px]">$</span>
                            <input wire:model="editAmount" type="number" step="0.01" min="0.01"
                                   class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg font-mono text-[13.5px] focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition-all @error('editAmount') border-red-400 @enderror">
                        </div>
                        @error('editAmount') <p class="text-[11.5px] text-red-500 mt-1">{{ $message }}</p> @enderror
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
                    <i class="fa-regular fa-trash-can mr-1"></i>Remove this income instead
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
                    Updating just this occurrence leaves every other month of this recurring income untouched.
                </p>
                <button wire:click="saveEditOccurrence('this')" wire:loading.attr="disabled" wire:target="saveEditOccurrence"
                        class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all disabled:opacity-60">
                    <span class="text-[13px] font-semibold text-slate-800">This occurrence only</span>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </button>
            </div>

            <div class="px-5 py-4 border-t border-slate-100">
                <p class="text-[13.5px] text-slate-600 leading-relaxed mb-3">
                    Updating this and all future occurrences changes the recurring income permanently from this date onward.
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
                    <h2 class="text-[15px] font-bold text-slate-900">Remove Income</h2>
                </div>
                <button wire:click="cancelDeleteOccurrence()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-5 py-4">
                <p class="text-[13.5px] text-slate-600 leading-relaxed mb-3">
                    Removing just this occurrence leaves the recurring income and every other month untouched.
                </p>
                <button wire:click="deleteOccurrenceThisOnly()" wire:loading.attr="disabled" wire:target="deleteOccurrenceThisOnly"
                        class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all disabled:opacity-60">
                    <span class="text-[13px] font-semibold text-slate-800">This occurrence only</span>
                    <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                </button>
            </div>

            <div class="px-5 py-4 border-t border-slate-100">
                <p class="text-[13.5px] text-slate-600 leading-relaxed mb-3">
                    Removing this and all future occurrences ends the recurring income from this date onward.
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
