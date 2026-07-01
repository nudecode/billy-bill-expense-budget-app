<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Frequency;
use App\Models\RecurringIncome;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Recurring Income — Billy')]
class RecurringIncomeIndex extends Component
{
    #[Url]
    public string $search = '';

    // ── Create / Edit modal ───────────────────────────────────────────
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $frequencyId = '';

    public string $accountId = '';

    public string $amount = '';

    public string $startDate = '';

    public string $endDate = '';

    // ── Delete / End modal ────────────────────────────────────────────
    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public string $endDateChoice = '';

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->startDate = today()->toDateString();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $rule = RecurringIncome::where('user_id', auth()->id())->findOrFail($id);

        $this->editingId = $id;
        $this->name = $rule->name;
        $this->frequencyId = (string) $rule->frequency_id;
        $this->accountId = (string) $rule->account_id;
        $this->amount = number_format((float) $rule->amount, 2, '.', '');
        $this->startDate = $rule->start_date->toDateString();
        $this->endDate = $rule->end_date?->toDateString() ?? '';

        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:150',
            'frequencyId' => 'required|exists:frequencies,id',
            'accountId' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'startDate' => 'required|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
        ]);

        $data = [
            'user_id' => auth()->id(),
            'name' => $this->name,
            'frequency_id' => $this->frequencyId,
            'account_id' => $this->accountId,
            'amount' => $this->amount,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate ?: null,
        ];

        if ($this->editingId) {
            RecurringIncome::where('user_id', auth()->id())->findOrFail($this->editingId)->update($data);
            $this->dispatch('toast', message: 'Recurring income updated.', type: 'success');
        } else {
            RecurringIncome::create($data);
            $this->dispatch('toast', message: 'Recurring income created.', type: 'success');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        RecurringIncome::where('user_id', auth()->id())->findOrFail($id);
        $this->deletingId = $id;
        $this->endDateChoice = today()->toDateString();
        $this->showDeleteModal = true;
    }

    public function endToday(): void
    {
        RecurringIncome::where('user_id', auth()->id())->findOrFail($this->deletingId)
            ->update(['end_date' => today()->toDateString()]);
        $this->cancelDelete();
        $this->dispatch('toast', message: 'Recurring income ended today.', type: 'success');
    }

    public function endOnDate(): void
    {
        $this->validate(['endDateChoice' => 'required|date']);
        RecurringIncome::where('user_id', auth()->id())->findOrFail($this->deletingId)
            ->update(['end_date' => $this->endDateChoice]);
        $this->cancelDelete();
        $this->dispatch('toast', message: 'Recurring income end date set.', type: 'success');
    }

    public function deleteRule(): void
    {
        RecurringIncome::where('user_id', auth()->id())->findOrFail($this->deletingId)->delete();
        $this->cancelDelete();
        $this->dispatch('toast', message: 'Recurring income deleted.', type: 'success');
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->endDateChoice = '';
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->frequencyId = '';
        $this->accountId = '';
        $this->amount = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->resetValidation();
    }

    public function render()
    {
        $rules = RecurringIncome::with(['frequency', 'account'])
            ->where('user_id', auth()->id())
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByRaw('(end_date IS NULL) DESC, end_date DESC, start_date ASC')
            ->get();

        $frequencies = Frequency::orderBy('id')->get();
        $accounts = Account::where('user_id', auth()->id())->orderBy('name')->get();

        return view('livewire.recurring-income-index', compact(
            'rules', 'frequencies', 'accounts'
        ))->layout('layouts.app', ['title' => 'Recurring Income']);
    }
}
