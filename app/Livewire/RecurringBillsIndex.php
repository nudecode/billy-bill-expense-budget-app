<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Biller;
use App\Models\Category;
use App\Models\Frequency;
use App\Models\RecurringBill;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Recurring Bills — Billy')]
class RecurringBillsIndex extends Component
{
    #[Url]
    public string $search = '';

    // ── Create / Edit modal ───────────────────────────────────────────
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $billerId = '';

    public string $frequencyId = '';

    public string $categoryId = '';

    public string $accountId = '';

    public string $amount = '';

    public string $startDate = '';

    public string $endDate = '';

    // ── Delete / End modal ────────────────────────────────────────────
    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    public bool $hasPayments = false;

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
        $rule = RecurringBill::where('user_id', auth()->id())->findOrFail($id);

        $this->editingId = $id;
        $this->billerId = (string) $rule->biller_id;
        $this->frequencyId = (string) $rule->frequency_id;
        $this->categoryId = (string) $rule->category_id;
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

    public function setEndDateOffset(string $period): void
    {
        $anchor = $this->startDate ? Carbon::parse($this->startDate) : today();

        $this->endDate = match ($period) {
            '3m' => $anchor->copy()->addMonths(3)->toDateString(),
            '6m' => $anchor->copy()->addMonths(6)->toDateString(),
            '1y' => $anchor->copy()->addYear()->toDateString(),
            '2y' => $anchor->copy()->addYears(2)->toDateString(),
            default => $this->endDate,
        };
    }

    public function save(): void
    {
        $this->validate([
            'billerId' => 'required|exists:billers,id',
            'frequencyId' => 'required|exists:frequencies,id',
            'categoryId' => 'required|exists:categories,id',
            'accountId' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'startDate' => 'required|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
        ]);

        $data = [
            'user_id' => auth()->id(),
            'biller_id' => $this->billerId,
            'frequency_id' => $this->frequencyId,
            'category_id' => $this->categoryId,
            'account_id' => $this->accountId,
            'amount' => $this->amount,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate ?: null,
        ];

        if ($this->editingId) {
            RecurringBill::where('user_id', auth()->id())->findOrFail($this->editingId)->update($data);
            $this->dispatch('toast', message: 'Recurring bill updated.', type: 'success');
        } else {
            RecurringBill::create($data);
            $this->dispatch('toast', message: 'Recurring bill created.', type: 'success');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $rule = RecurringBill::where('user_id', auth()->id())->findOrFail($id);
        $this->deletingId = $id;
        $this->hasPayments = $rule->payments()->exists();
        $this->endDateChoice = today()->toDateString();
        $this->showDeleteModal = true;
    }

    public function endToday(): void
    {
        RecurringBill::where('user_id', auth()->id())->findOrFail($this->deletingId)
            ->update(['end_date' => today()->toDateString()]);
        $this->cancelDelete();
        $this->dispatch('toast', message: 'Recurring bill ended today.', type: 'success');
    }

    public function endOnDate(): void
    {
        $this->validate(['endDateChoice' => 'required|date']);
        RecurringBill::where('user_id', auth()->id())->findOrFail($this->deletingId)
            ->update(['end_date' => $this->endDateChoice]);
        $this->cancelDelete();
        $this->dispatch('toast', message: 'Recurring bill end date set.', type: 'success');
    }

    public function deleteRule(): void
    {
        RecurringBill::where('user_id', auth()->id())->findOrFail($this->deletingId)->delete();
        $this->cancelDelete();
        $this->dispatch('toast', message: 'Recurring bill deleted.', type: 'success');
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->hasPayments = false;
        $this->endDateChoice = '';
    }

    private function resetForm(): void
    {
        $this->billerId = '';
        $this->frequencyId = '';
        $this->categoryId = '';
        $this->accountId = '';
        $this->amount = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->resetValidation();
    }

    public function render()
    {
        $rules = RecurringBill::with(['biller', 'frequency', 'category', 'account'])
            ->where('user_id', auth()->id())
            ->when($this->search, fn ($q) => $q->whereHas('biller', fn ($b) => $b->where('name', 'like', "%{$this->search}%")))
            ->orderByRaw('(end_date IS NULL) DESC, end_date DESC, start_date ASC')
            ->get();

        $billers = Biller::where('user_id', auth()->id())->orderBy('name')->get();
        $frequencies = Frequency::orderBy('id')->get();
        $categories = Category::orderBy('name')->get();
        $accounts = Account::where('user_id', auth()->id())->orderBy('name')->get();

        return view('livewire.recurring-bills-index', compact(
            'rules', 'billers', 'frequencies', 'categories', 'accounts'
        ))->layout('layouts.app', ['title' => 'Recurring Bills']);
    }
}
