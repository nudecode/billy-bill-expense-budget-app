<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Category;
use App\Models\Payment;
use App\Models\RecurringBill;
use App\Models\RecurringBillOverride;
use App\Services\RecurringBillService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Bills — Billy')]
class BillsIndex extends Component
{
    #[Url(as: 'y')]
    public int $year;

    #[Url(as: 'm')]
    public int $month;

    #[Url]
    public string $calView = 'week';   // 'week' | 'month'

    #[Url]
    public string $tab = 'all';

    #[Url]
    public string $search = '';

    #[Url]
    public string $selectedDate = '';  // Y-m-d, empty = show all

    // ── Pay modal state ───────────────────────────────────────────────────
    public bool $showPayModal = false;

    public ?int $payingRuleId = null;

    #[Rule('required|date')]
    public string $payDate = '';

    #[Rule('required|numeric|min:0')]
    public string $payAmount = '';

    #[Rule('nullable|string|max:100')]
    public string $payReference = '';

    #[Rule('nullable|string|max:1000')]
    public string $payNotes = '';

    // ── Edit occurrence modal (unpaid bills only) ───────────────────────────
    public bool $showEditModal = false;

    public ?int $editingRuleId = null;

    public string $editingDate = '';

    public string $editAmount = '';

    public string $editCategoryId = '';

    public string $editAccountId = '';

    // ── Delete occurrence modal (unpaid bills only) ─────────────────────────
    public bool $showDeleteOccurrenceModal = false;

    public ?int $deletingOccurrenceRuleId = null;

    public string $deletingOccurrenceDate = '';

    public function mount(): void
    {
        $this->year ??= now()->year;
        $this->month ??= now()->month;
        if (! $this->selectedDate) {
            $this->selectedDate = now()->toDateString();
        }
    }

    public function selectDate(string $date): void
    {
        // Tap same date again = deselect (show all)
        $this->selectedDate = ($this->selectedDate === $date) ? '' : $date;
    }

    public function previousMonth(): void
    {
        $this->selectedDate = '';
        if ($this->month === 1) {
            $this->month = 12;
            $this->year--;
        } else {
            $this->month--;
        }
    }

    public function nextMonth(): void
    {
        $this->selectedDate = '';
        if ($this->month === 12) {
            $this->month = 1;
            $this->year++;
        } else {
            $this->month++;
        }
    }

    public function previousWeek(): void
    {
        $anchor = $this->resolveWeekAnchor();
        $date = Carbon::parse($anchor)->subWeek();
        $this->selectedDate = $date->toDateString();
        $this->year = $date->year;
        $this->month = $date->month;
    }

    public function nextWeek(): void
    {
        $anchor = $this->resolveWeekAnchor();
        $date = Carbon::parse($anchor)->addWeek();
        $this->selectedDate = $date->toDateString();
        $this->year = $date->year;
        $this->month = $date->month;
    }

    public function openPayModal(int $ruleId, string $date): void
    {
        $rule = RecurringBill::where('user_id', auth()->id())->findOrFail($ruleId);

        $this->payingRuleId = $ruleId;
        $this->payDate = $date;
        $this->payAmount = number_format((float) $rule->amount, 2, '.', '');
        $this->payReference = '';
        $this->payNotes = '';
        $this->resetValidation();
        $this->showPayModal = true;
    }

    public function closePayModal(): void
    {
        $this->showPayModal = false;
        $this->payingRuleId = null;
        $this->resetValidation();
    }

    public function savePayment(): void
    {
        $this->validate();

        $user = auth()->user();
        $rule = RecurringBill::where('user_id', $user->id)->findOrFail($this->payingRuleId);

        $alreadyPaid = Payment::where('user_id', $user->id)
            ->where('recurring_bill_id', $rule->id)
            ->where('recurring_bill_date', $this->payDate)
            ->exists();

        if (! $alreadyPaid) {
            Payment::create([
                'user_id' => $user->id,
                'recurring_bill_id' => $rule->id,
                'recurring_bill_date' => $this->payDate,
                'biller_id' => $rule->biller_id,
                'account_id' => $rule->account_id,
                'category_id' => $rule->category_id,
                'amount' => $this->payAmount,
                'payment_date' => $this->payDate,
                'reference_number' => $this->payReference ?: null,
                'notes' => $this->payNotes ?: null,
            ]);
        }

        app(RecurringBillService::class)->clearCache($user->id, $this->year, $this->month);
        $this->closePayModal();
        $this->dispatch('toast', message: 'Payment recorded.', type: 'success');
    }

    public function openEditOccurrence(int $ruleId, string $date): void
    {
        $rule = RecurringBill::where('user_id', auth()->id())->findOrFail($ruleId);

        $override = RecurringBillOverride::where('recurring_bill_id', $ruleId)
            ->where('occurrence_date', $date)
            ->first();

        $this->editingRuleId = $ruleId;
        $this->editingDate = $date;
        $this->editAmount = number_format((float) ($override->amount ?? $rule->amount), 2, '.', '');
        $this->editCategoryId = (string) ($override->category_id ?? $rule->category_id);
        $this->editAccountId = (string) ($override->account_id ?? $rule->account_id);
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingRuleId = null;
        $this->editingDate = '';
        $this->resetValidation();
    }

    public function saveEditOccurrence(string $scope): void
    {
        $this->validate([
            'editAmount' => 'required|numeric|min:0.01',
            'editCategoryId' => 'required|exists:categories,id',
            'editAccountId' => 'required|exists:accounts,id',
        ]);

        $rule = RecurringBill::where('user_id', auth()->id())->findOrFail($this->editingRuleId);

        if ($scope === 'future') {
            $this->splitRuleForFutureEdit($rule);
        } else {
            RecurringBillOverride::updateOrCreate(
                ['recurring_bill_id' => $rule->id, 'occurrence_date' => $this->editingDate],
                ['is_skipped' => false, 'amount' => $this->editAmount, 'category_id' => $this->editCategoryId, 'account_id' => $this->editAccountId]
            );
        }

        app(RecurringBillService::class)->clearCache(auth()->id(), $this->year, $this->month);
        $this->closeEditModal();
        $this->dispatch('toast', message: 'Bill updated.', type: 'success');
    }

    private function splitRuleForFutureEdit(RecurringBill $rule): void
    {
        $splitDate = Carbon::parse($this->editingDate);
        $originalEndDate = $rule->end_date?->toDateString();

        $rule->update(['end_date' => $splitDate->copy()->subDay()->toDateString()]);

        RecurringBill::create([
            'user_id' => $rule->user_id,
            'biller_id' => $rule->biller_id,
            'frequency_id' => $rule->frequency_id,
            'category_id' => $this->editCategoryId,
            'subcategory_id' => $rule->subcategory_id,
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
        RecurringBill::where('user_id', auth()->id())->findOrFail($ruleId);
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
        RecurringBillOverride::updateOrCreate(
            ['recurring_bill_id' => $this->deletingOccurrenceRuleId, 'occurrence_date' => $this->deletingOccurrenceDate],
            ['is_skipped' => true]
        );

        app(RecurringBillService::class)->clearCache(auth()->id(), $this->year, $this->month);
        $this->cancelDeleteOccurrence();
        $this->dispatch('toast', message: 'Occurrence removed.', type: 'success');
    }

    public function deleteOccurrenceAllFuture(): void
    {
        $rule = RecurringBill::where('user_id', auth()->id())->findOrFail($this->deletingOccurrenceRuleId);
        $rule->update(['end_date' => Carbon::parse($this->deletingOccurrenceDate)->subDay()->toDateString()]);

        app(RecurringBillService::class)->clearCache(auth()->id(), $this->year, $this->month);
        $this->cancelDeleteOccurrence();
        $this->dispatch('toast', message: 'Bill ended from this date onward.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user();
        $service = app(RecurringBillService::class);
        $instances = $service->getForMonth($user->id, $this->year, $this->month);
        $today = now()->toDateString();

        // Build per-day status lookup for calendar indicators
        $dayStatus = [];
        foreach ($instances as $inst) {
            $key = $inst->date->toDateString();
            $dayStatus[$key] ??= ['all_paid' => true, 'count' => 0];
            $dayStatus[$key]['count']++;
            if (! $inst->isPaid) {
                $dayStatus[$key]['all_paid'] = false;
            }
        }

        // ── Week strip data ────────────────────────────────────────────
        $weekAnchor = $this->resolveWeekAnchor();
        $weekStart = Carbon::parse($weekAnchor)->startOfWeek(Carbon::MONDAY);
        $weekDays = collect(range(0, 6))
            ->map(fn ($i) => $weekStart->copy()->addDays($i)->toDateString());

        // ── Month grid data (6 rows × 7 cols) ─────────────────────────
        $firstOfMonth = Carbon::create($this->year, $this->month, 1);
        $gridStart = $firstOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calDays = collect(range(0, 41))
            ->map(fn ($i) => $gridStart->copy()->addDays($i)->toDateString());

        // ── Filtered bill list ─────────────────────────────────────────
        $listInstances = collect($instances);

        if ($this->selectedDate) {
            $listInstances = $listInstances->filter(
                fn ($b) => $b->date->toDateString() === $this->selectedDate
            );
        } else {
            // Apply tab and search only when not filtered to a single day
            if ($this->search) {
                $s = strtolower($this->search);
                $listInstances = $listInstances->filter(
                    fn ($b) => str_contains(strtolower($b->getBillerName()), $s)
                );
            }
            if ($this->tab === 'paid') {
                $listInstances = $listInstances->filter(fn ($b) => $b->isPaid);
            } elseif ($this->tab === 'unpaid') {
                $listInstances = $listInstances->filter(fn ($b) => ! $b->isPaid);
            }
        }

        $listInstances = $listInstances->values()->all();

        // ── Month totals (always full month, unaffected by day selection) ─
        $paidTotal = collect($instances)->filter(fn ($b) => $b->isPaid)->sum(fn ($b) => $b->getAmount());
        $unpaidTotal = collect($instances)->filter(fn ($b) => ! $b->isPaid)->sum(fn ($b) => $b->getAmount());
        $periodLabel = $firstOfMonth->format('F Y');

        $categories = Category::orderBy('name')->get();
        $accounts = Account::where('user_id', $user->id)->orderBy('name')->get();

        return view('livewire.bills-index', compact(
            'instances', 'listInstances', 'dayStatus', 'today',
            'weekDays', 'calDays', 'paidTotal', 'unpaidTotal', 'periodLabel',
            'categories', 'accounts'
        ))->layout('layouts.app', ['title' => 'Bills']);
    }

    private function resolveWeekAnchor(): string
    {
        if ($this->selectedDate) {
            return $this->selectedDate;
        }
        $today = now()->toDateString();

        // If today is in the current month, use it; otherwise use first of month
        return now()->year === $this->year && now()->month === $this->month
            ? $today
            : Carbon::create($this->year, $this->month, 1)->toDateString();
    }
}
