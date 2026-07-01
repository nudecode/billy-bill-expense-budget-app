<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\OneOffIncome;
use App\Models\RecurringIncome;
use App\Models\RecurringIncomeOverride;
use App\Services\RecurringIncomeService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Income — Billy')]
class IncomeIndex extends Component
{
    #[Url(as: 'y')]
    public int $year;

    #[Url(as: 'm')]
    public int $month;

    // ── Edit occurrence modal ────────────────────────────────────────────
    public bool $showEditModal = false;

    public bool $showEditScopeModal = false;

    public ?int $editingRuleId = null;

    public string $editingDate = '';

    public string $editAmount = '';

    public string $editAccountId = '';

    public string $editOriginalAmount = '';

    public string $editOriginalAccountId = '';

    // ── Delete occurrence modal ──────────────────────────────────────────
    public bool $showDeleteOccurrenceModal = false;

    public ?int $deletingOccurrenceRuleId = null;

    public string $deletingOccurrenceDate = '';

    public function mount(): void
    {
        $this->year ??= now()->year;
        $this->month ??= now()->month;
    }

    public function previousMonth(): void
    {
        if ($this->month === 1) {
            $this->month = 12;
            $this->year--;
        } else {
            $this->month--;
        }
    }

    public function nextMonth(): void
    {
        if ($this->month === 12) {
            $this->month = 1;
            $this->year++;
        } else {
            $this->month++;
        }
    }

    public function openEditOccurrence(int $ruleId, string $date): void
    {
        $rule = RecurringIncome::where('user_id', auth()->id())->findOrFail($ruleId);

        $override = RecurringIncomeOverride::where('recurring_income_id', $ruleId)
            ->whereDate('occurrence_date', $date)
            ->first();

        $this->editingRuleId = $ruleId;
        $this->editingDate = $date;
        $this->editAmount = number_format((float) ($override->amount ?? $rule->amount), 2, '.', '');
        $this->editAccountId = (string) ($override->account_id ?? $rule->account_id);
        $this->editOriginalAmount = $this->editAmount;
        $this->editOriginalAccountId = $this->editAccountId;
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->showEditScopeModal = false;
        $this->editingRuleId = null;
        $this->editingDate = '';
        $this->resetValidation();
    }

    public function proceedFromEdit(): void
    {
        $this->validate([
            'editAmount' => 'required|numeric|min:0.01',
            'editAccountId' => 'required|exists:accounts,id',
        ]);

        $unchanged = number_format((float) $this->editAmount, 2, '.', '') === number_format((float) $this->editOriginalAmount, 2, '.', '')
            && $this->editAccountId === $this->editOriginalAccountId;

        if ($unchanged) {
            $this->closeEditModal();
            $this->dispatch('toast', message: 'No changes made.', type: 'success');

            return;
        }

        $this->showEditModal = false;
        $this->showEditScopeModal = true;
    }

    public function cancelEditScope(): void
    {
        $this->showEditScopeModal = false;
        $this->editingRuleId = null;
        $this->editingDate = '';
    }

    public function saveEditOccurrence(string $scope): void
    {
        $this->validate([
            'editAmount' => 'required|numeric|min:0.01',
            'editAccountId' => 'required|exists:accounts,id',
        ]);

        $rule = RecurringIncome::where('user_id', auth()->id())->findOrFail($this->editingRuleId);

        if ($scope === 'future') {
            $this->splitRuleForFutureEdit($rule);
        } else {
            $override = $this->findOrNewOverride($rule->id, $this->editingDate);
            $override->fill([
                'is_skipped' => false,
                'amount' => $this->editAmount,
                'account_id' => $this->editAccountId,
            ]);
            $override->save();
        }

        app(RecurringIncomeService::class)->clearCache(auth()->id(), $this->year, $this->month);
        $this->showEditScopeModal = false;
        $this->editingRuleId = null;
        $this->editingDate = '';
        $this->dispatch('toast', message: 'Income updated.', type: 'success');
    }

    private function findOrNewOverride(int $ruleId, string $date): RecurringIncomeOverride
    {
        return RecurringIncomeOverride::where('recurring_income_id', $ruleId)
            ->whereDate('occurrence_date', $date)
            ->first() ?? new RecurringIncomeOverride(['recurring_income_id' => $ruleId, 'occurrence_date' => $date]);
    }

    private function splitRuleForFutureEdit(RecurringIncome $rule): void
    {
        $splitDate = Carbon::parse($this->editingDate);
        $originalEndDate = $rule->end_date?->toDateString();

        $rule->update(['end_date' => $splitDate->copy()->subDay()->toDateString()]);

        RecurringIncome::create([
            'user_id' => $rule->user_id,
            'name' => $rule->name,
            'frequency_id' => $rule->frequency_id,
            'account_id' => $this->editAccountId,
            'amount' => $this->editAmount,
            'start_date' => $splitDate->toDateString(),
            'end_date' => $originalEndDate,
        ]);
    }

    public function switchToDeleteOccurrence(): void
    {
        $ruleId = $this->editingRuleId;
        $date = $this->editingDate;
        $this->closeEditModal();
        $this->confirmDeleteOccurrence($ruleId, $date);
    }

    public function confirmDeleteOccurrence(int $ruleId, string $date): void
    {
        RecurringIncome::where('user_id', auth()->id())->findOrFail($ruleId);
        $this->deletingOccurrenceRuleId = $ruleId;
        $this->deletingOccurrenceDate = $date;
        $this->showDeleteOccurrenceModal = true;
    }

    public function cancelDeleteOccurrence(): void
    {
        $this->showDeleteOccurrenceModal = false;
        $this->deletingOccurrenceRuleId = null;
        $this->deletingOccurrenceDate = '';
    }

    public function deleteOccurrenceThisOnly(): void
    {
        $override = $this->findOrNewOverride($this->deletingOccurrenceRuleId, $this->deletingOccurrenceDate);
        $override->is_skipped = true;
        $override->save();

        app(RecurringIncomeService::class)->clearCache(auth()->id(), $this->year, $this->month);
        $this->cancelDeleteOccurrence();
        $this->dispatch('toast', message: 'Occurrence removed.', type: 'success');
    }

    public function deleteOccurrenceAllFuture(): void
    {
        $rule = RecurringIncome::where('user_id', auth()->id())->findOrFail($this->deletingOccurrenceRuleId);
        $rule->update(['end_date' => Carbon::parse($this->deletingOccurrenceDate)->subDay()->toDateString()]);

        app(RecurringIncomeService::class)->clearCache(auth()->id(), $this->year, $this->month);
        $this->cancelDeleteOccurrence();
        $this->dispatch('toast', message: 'Income ended from this date onward.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user();
        $service = app(RecurringIncomeService::class);
        $instances = $service->getForMonth($user->id, $this->year, $this->month);

        $oneOff = OneOffIncome::with('account')
            ->where('user_id', $user->id)
            ->whereYear('income_date', $this->year)
            ->whereMonth('income_date', $this->month)
            ->get();

        $total = collect($instances)->sum(fn ($i) => $i->getAmount()) + $oneOff->sum('amount');
        $periodLabel = Carbon::create($this->year, $this->month, 1)->format('F Y');

        $accounts = Account::where('user_id', $user->id)->orderBy('name')->get();

        return view('livewire.income-index', compact('instances', 'oneOff', 'total', 'periodLabel', 'accounts'))->layout('layouts.app', ['title' => 'Income']);
    }
}
