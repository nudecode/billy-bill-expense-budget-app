<div>

    {{-- ── TOOLBAR ─────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="relative">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search billers…" class="pl-9 pr-4 py-1.5 border border-slate-200 rounded-lg text-[13px] bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 outline-none w-52">
        </div>
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition-colors">
            <i class="fa-solid fa-plus text-xs"></i> Add Biller
        </button>
    </div>

    {{-- ── TABLE ───────────────────────────────────────────────── --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Name</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Email</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden md:table-cell">Phone</th>
                        <th class="text-right px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Total Paid</th>
                        <th class="px-2 sm:px-5 py-3 w-20 sm:w-24"></th>
                    </tr>
                </thead>
                <tbody>
                    @if($grouped)
                        @foreach($grouped as $letter => $group)
                        <tr @click="open['{{ $letter }}'] = !(open['{{ $letter }}'] ?? true)"
                            class="bg-gray-50 border-y border-slate-100 cursor-pointer select-none hover:bg-gray-100 transition-colors">
                            <td colspan="5" class="px-5 py-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[12px] font-bold text-gray-600">{{ $letter }}</span>
                                        <span class="text-[11px] text-gray-400 font-medium">({{ $group->count() }})</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-[11px] transition-transform duration-200"
                                       :class="(open['{{ $letter }}'] ?? true) ? 'rotate-0' : '-rotate-90'"></i>
                                </div>
                            </td>
                        </tr>
                        @foreach($group as $biller)
                        @include('livewire.partials.biller-row', ['biller' => $biller])
                        @endforeach
                        @endforeach
                    @else
                        @forelse($billers as $biller)
                            @include('livewire.partials.biller-row', ['biller' => $biller])
                        @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-[13px]">No billers match your search.</td></tr>
                        @endforelse
                    @endif
                    @if($billers->isEmpty() && !$search)
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-[13px]">No billers yet. Add your first biller to get started.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── CREATE / EDIT MODAL ─────────────────────────────────── --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.closeModal()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition.opacity
             @click="$wire.closeModal()"></div>

        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl z-10 max-h-[90vh] overflow-y-auto"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-[15px] font-bold text-gray-900">{{ $editingId ? 'Edit Biller' : 'Add Biller' }}</h2>
                <button wire:click="closeModal" class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-[11.5px] font-semibold text-gray-600 mb-1.5">Biller Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" placeholder="e.g. Netflix, Unity Water"
                           class="w-full px-3 py-2.5 border rounded-lg text-[13.5px] outline-none transition-all
                                  {{ $errors->has('name') ? 'border-red-400 bg-red-50 focus:ring-2 focus:ring-red-200' : 'border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-100' }}">
                    @error('name') <p class="text-red-500 text-[11.5px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11.5px] font-semibold text-gray-600 mb-1.5">Phone <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input wire:model="phone" type="text" placeholder="e.g. 13 22 00"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-[13.5px] outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 transition-all">
                        @error('phone') <p class="text-red-500 text-[11.5px] mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-semibold text-gray-600 mb-1.5">Email <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input wire:model="email" type="email" placeholder="billing@example.com"
                               class="w-full px-3 py-2.5 border rounded-lg text-[13.5px] outline-none transition-all
                                      {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:ring-2 focus:ring-red-200' : 'border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-100' }}">
                        @error('email') <p class="text-red-500 text-[11.5px] mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-[11.5px] font-semibold text-gray-600 mb-1.5">Account / Reference Number <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input wire:model="accountNumber" type="text" placeholder="Your account number with this biller"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-[13.5px] outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 transition-all">
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                <button wire:click="closeModal" class="px-4 py-2 text-[13px] font-semibold text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button wire:click="save" class="px-4 py-2 text-[13px] font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Add Biller' }}</span>
                    <span wire:loading wire:target="save"><i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Saving…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── DELETE CONFIRMATION MODAL ───────────────────────────── --}}
    @if($showDeleteModal)
    @php $billerToDelete = $deletingId ? \App\Models\Biller::find($deletingId) : null; @endphp
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         x-data="{ open: false }"
         x-init="$nextTick(() => open = true)"
         @keydown.escape.window="$wire.cancelDelete()">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             x-show="open" x-transition.opacity
             @click="$wire.cancelDelete()"></div>

        <div class="relative bg-white w-full sm:max-w-sm rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">

            <div class="p-6">
                @if($deleteBlockReason)
                    <div class="flex gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-gray-900 mb-1">Cannot delete biller</h3>
                            <p class="text-[13px] text-gray-600">{{ $deleteBlockReason }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button wire:click="cancelDelete" class="px-4 py-2 text-[13px] font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">Got it</button>
                    </div>
                @else
                    <div class="flex gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-trash text-red-600"></i>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-gray-900 mb-1">Delete biller?</h3>
                            <p class="text-[13px] text-gray-600">
                                <strong>{{ $billerToDelete?->name }}</strong> will be removed.
                                Past payment history will be kept but will no longer be linked to this biller.
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button wire:click="cancelDelete" class="px-4 py-2 text-[13px] font-semibold text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        <button wire:click="deleteBiller" class="px-4 py-2 text-[13px] font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                            <span wire:loading.remove wire:target="deleteBiller">Delete</span>
                            <span wire:loading wire:target="deleteBiller"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>
