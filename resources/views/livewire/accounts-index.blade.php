<div>

    {{-- ── TOOLBAR ─────────────────────────────────────────────── --}}
    <div class="flex justify-end mb-6">
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition-colors">
            <i class="fa-solid fa-plus text-xs"></i> Add Account
        </button>
    </div>

    {{-- ── TABLE ───────────────────────────────────────────────── --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Account Name</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($accounts as $account)
                <tr class="hover:bg-slate-50/50 group">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-[12px] font-bold text-blue-600">
                                {{ strtoupper(substr($account->name, 0, 1)) }}
                            </div>
                            <div class="text-[13.5px] font-semibold text-slate-900">{{ $account->name }}</div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-1 justify-end lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                            <button wire:click="openEdit({{ $account->id }})"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all"
                                    title="Edit">
                                <i class="fa-regular fa-pen-to-square text-sm"></i>
                            </button>
                            <button wire:click="confirmDelete({{ $account->id }})"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all"
                                    title="Delete">
                                <i class="fa-regular fa-trash-can text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="2" class="px-5 py-10 text-center text-slate-400 text-[13px]">No accounts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
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

        <div class="relative bg-white w-full sm:max-w-sm rounded-t-2xl sm:rounded-2xl shadow-xl z-10"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-[15px] font-bold text-gray-900">{{ $editingId ? 'Edit Account' : 'Add Account' }}</h2>
                <button wire:click="closeModal" class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="px-6 py-5">
                <label class="block text-[11.5px] font-semibold text-gray-600 mb-1.5">Account Name <span class="text-red-500">*</span></label>
                <input wire:model="name" type="text" placeholder="e.g. Direct Debit, Savings Account"
                       class="w-full px-3 py-2.5 border rounded-lg text-[13.5px] outline-none transition-all
                              {{ $errors->has('name') ? 'border-red-400 bg-red-50 focus:ring-2 focus:ring-red-200' : 'border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-100' }}">
                @error('name') <p class="text-red-500 text-[11.5px] mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                <button wire:click="closeModal" class="px-4 py-2 text-[13px] font-semibold text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                <button wire:click="save" class="px-4 py-2 text-[13px] font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Add Account' }}</span>
                    <span wire:loading wire:target="save"><i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Saving…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── DELETE CONFIRMATION MODAL ───────────────────────────── --}}
    @if($showDeleteModal)
    @php $accountToDelete = $deletingId ? \App\Models\Account::find($deletingId) : null; @endphp
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
                            <h3 class="text-[15px] font-bold text-gray-900 mb-1">Cannot delete account</h3>
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
                            <h3 class="text-[15px] font-bold text-gray-900 mb-1">Delete account?</h3>
                            <p class="text-[13px] text-gray-600"><strong>{{ $accountToDelete?->name }}</strong> will be permanently removed.</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button wire:click="cancelDelete" class="px-4 py-2 text-[13px] font-semibold text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        <button wire:click="deleteAccount" class="px-4 py-2 text-[13px] font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                            <span wire:loading.remove wire:target="deleteAccount">Delete</span>
                            <span wire:loading wire:target="deleteAccount"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>
