<?php

namespace App\Livewire;

use App\Models\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Accounts — Billy')]
class AccountsIndex extends Component
{
    // ── Modal state ───────────────────────────────────────────────
    public bool   $showModal       = false;
    public bool   $showDeleteModal = false;
    public ?int   $editingId       = null;
    public ?int   $deletingId      = null;
    public string $deleteBlockReason = '';

    // ── Form fields ───────────────────────────────────────────────
    public string $name = '';

    protected function rules(): array
    {
        return ['name' => 'required|string|max:100'];
    }

    protected function messages(): array
    {
        return ['name.required' => 'Account name is required.'];
    }

    // ── Create ────────────────────────────────────────────────────
    public function openCreate(): void
    {
        $this->name      = '';
        $this->editingId = null;
        $this->showModal = true;
        $this->resetErrorBag();
    }

    // ── Edit ──────────────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $account = Account::where('user_id', auth()->id())->findOrFail($id);
        $this->editingId = $id;
        $this->name      = $account->name;
        $this->showModal = true;
        $this->resetErrorBag();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->name      = '';
        $this->resetErrorBag();
    }

    // ── Save ──────────────────────────────────────────────────────
    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            Account::where('user_id', auth()->id())
                ->findOrFail($this->editingId)
                ->update(['name' => trim($this->name)]);
            $message = "'{$this->name}' updated.";
        } else {
            Account::create(['user_id' => auth()->id(), 'name' => trim($this->name)]);
            $message = "'{$this->name}' added.";
        }

        $this->closeModal();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    // ── Delete ────────────────────────────────────────────────────
    public function confirmDelete(int $id): void
    {
        $account = Account::where('user_id', auth()->id())->findOrFail($id);

        $linkedCount = $account->recurringBills()->count()
            + $account->oneOffBills()->count()
            + $account->recurringIncome()->count();

        $this->deleteBlockReason = $linkedCount > 0
            ? "This account is used by {$linkedCount} " . ($linkedCount === 1 ? 'bill or income record' : 'bills or income records') . ". Remove those first."
            : '';

        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal   = false;
        $this->deletingId        = null;
        $this->deleteBlockReason = '';
    }

    public function deleteAccount(): void
    {
        if ($this->deleteBlockReason) {
            return;
        }

        $account = Account::where('user_id', auth()->id())->findOrFail($this->deletingId);
        $name    = $account->name;
        $account->delete();

        $this->cancelDelete();
        $this->dispatch('toast', message: "'{$name}' deleted.", type: 'success');
    }

    // ── Render ────────────────────────────────────────────────────
    public function render()
    {
        $accounts = Account::where('user_id', auth()->id())->orderBy('name')->get();
        return view('livewire.accounts-index', compact('accounts'))
            ->layout('layouts.app', ['title' => 'Accounts']);
    }
}
