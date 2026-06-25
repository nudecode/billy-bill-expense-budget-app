<?php

namespace App\Livewire;

use App\Models\Biller;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Billers — Billy')]
class BillersIndex extends Component
{
    // ── List state ────────────────────────────────────────────────
    #[Url] public string $search = '';

    // ── Modal state ───────────────────────────────────────────────
    public bool   $showModal       = false;
    public bool   $showDeleteModal = false;
    public ?int   $editingId       = null;
    public ?int   $deletingId      = null;
    public string $deleteBlockReason = '';

    // ── Form fields ───────────────────────────────────────────────
    public string $name          = '';
    public string $phone         = '';
    public string $email         = '';
    public string $accountNumber = '';

    protected function rules(): array
    {
        return [
            'name'          => 'required|string|max:150',
            'phone'         => 'nullable|string|max:30',
            'email'         => 'nullable|email|max:150',
            'accountNumber' => 'nullable|string|max:100',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Biller name is required.',
            'email.email'   => 'Please enter a valid email address.',
        ];
    }

    // ── Create ────────────────────────────────────────────────────
    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId  = null;
        $this->showModal  = true;
    }

    // ── Edit ──────────────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $biller = Biller::where('user_id', auth()->id())->findOrFail($id);

        $this->editingId     = $id;
        $this->name          = $biller->name;
        $this->phone         = $biller->phone ?? '';
        $this->email         = $biller->email ?? '';
        $this->accountNumber = $biller->account_number ?? '';
        $this->showModal     = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    // ── Save (create or update) ───────────────────────────────────
    public function save(): void
    {
        $this->validate();

        $data = [
            'name'           => trim($this->name),
            'phone'          => $this->phone ?: null,
            'email'          => $this->email ?: null,
            'account_number' => $this->accountNumber ?: null,
        ];

        if ($this->editingId) {
            Biller::where('user_id', auth()->id())
                ->findOrFail($this->editingId)
                ->update($data);
            $message = "'{$data['name']}' updated.";
        } else {
            Biller::create(['user_id' => auth()->id(), ...$data]);
            $message = "'{$data['name']}' added.";
        }

        $this->closeModal();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    // ── Delete ────────────────────────────────────────────────────
    public function confirmDelete(int $id): void
    {
        $biller = Biller::where('user_id', auth()->id())->findOrFail($id);

        $activeRecurring = $biller->recurringBills()
            ->where('end_date', '>=', today())
            ->count();

        if ($activeRecurring > 0) {
            $this->deleteBlockReason = "This biller has {$activeRecurring} active recurring " .
                ($activeRecurring === 1 ? 'bill' : 'bills') .
                ". End or delete those first before removing the biller.";
        } else {
            $this->deleteBlockReason = '';
        }

        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal    = false;
        $this->deletingId         = null;
        $this->deleteBlockReason  = '';
    }

    public function deleteBiller(): void
    {
        if ($this->deleteBlockReason) {
            return;
        }

        $biller = Biller::where('user_id', auth()->id())->findOrFail($this->deletingId);
        $name   = $biller->name;
        $biller->delete();

        $this->cancelDelete();
        $this->dispatch('toast', message: "'{$name}' deleted.", type: 'success');
    }

    // ── Render ────────────────────────────────────────────────────
    public function render()
    {
        $billers = Biller::where('user_id', auth()->id())
            ->withSum('payments', 'amount')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        $grouped = $this->search
            ? null
            : $billers->groupBy(fn ($b) => strtoupper(substr($b->name, 0, 1)));

        return view('livewire.billers-index', compact('billers', 'grouped'))
            ->layout('layouts.app', ['title' => 'Billers']);
    }

    private function resetForm(): void
    {
        $this->name          = '';
        $this->phone         = '';
        $this->email         = '';
        $this->accountNumber = '';
        $this->resetErrorBag();
    }
}
