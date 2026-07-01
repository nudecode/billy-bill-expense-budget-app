<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Biller;
use App\Models\Category;
use App\Models\Frequency;
use App\Models\OneOffBill;
use App\Models\Payment;
use App\Models\RecurringBill;
use App\Models\RecurringBillOverride;
use App\Models\Subcategory;
use App\Services\RecurringBillService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
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

    public string $payType = 'recurring'; // 'recurring' | 'oneoff'

    public ?int $payingRuleId = null;

    public ?int $payOneOffId = null;

    public string $payDate = '';

    public string $payAmount = '';

    public string $payReference = '';

    public string $payNotes = '';

    // ── Payment detail modal (paid bills, both types) ───────────────────────
    public bool $showPaymentDetailModal = false;

    public string $viewingDetailType = 'recurring'; // 'recurring' | 'oneoff'

    public ?int $viewingDetailId = null;

    public string $viewingDetailDate = '';

    // ── Edit occurrence modal (unpaid bills only) ───────────────────────────
    public bool $showEditModal = false;

    public bool $showEditScopeModal = false;

    public ?int $editingRuleId = null;

    public string $editingDate = '';

    public string $editAmount = '';

    public string $editCategoryId = '';

    public string $editAccountId = '';

    public string $editOriginalAmount = '';

    public string $editOriginalCategoryId = '';

    public string $editOriginalAccountId = '';

    // ── Delete occurrence modal (unpaid bills only) ─────────────────────────
    public bool $showDeleteOccurrenceModal = false;

    public ?int $deletingOccurrenceRuleId = null;

    public string $deletingOccurrenceDate = '';

    // ── Add/Edit Bill modal (one-off + recurring toggle) ────────────────────
    public bool $showBillModal = false;

    public bool $isRecurring = false;

    public ?int $editingOneOffId = null;

    public string $billBillerId = '';

    public string $billerSearch = '';

    public string $billAmount = '';

    public string $billDueDate = '';

    public string $billCategoryId = '';

    public string $billSubcategoryId = '';

    public string $billAccountId = '';

    public string $billFrequencyId = '';

    public string $billStartDate = '';

    public string $billEndDate = '';

    // ── Delete one-off bill modal ────────────────────────────────────────────
    public bool $showDeleteOneOffModal = false;

    public ?int $deletingOneOffId = null;

    // ── Recurring-prompt nudge modal ─────────────────────────────────────────
    public bool $showRecurringPromptModal = false;

    public ?int $promptBillerId = null;

    public string $promptBillerName = '';

    // ── Quick-add biller modal ────────────────────────────────────────────────
    public bool $showQuickAddBillerModal = false;

    public string $quickBillerName = '';

    public function mount(): void
    {
        $this->year ??= now()->year;
        $this->month ??= now()->month;
        if (! $this->selectedDate && ! request()->has('selectedDate')) {
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

    public function openPayModal(string $type, int $id, ?string $date = null): void
    {
        $this->payType = $type;
        $this->payReference = '';
        $this->payNotes = '';

        if ($type === 'oneoff') {
            $bill = OneOffBill::where('user_id', auth()->id())->findOrFail($id);
            $this->payOneOffId = $id;
            $this->payingRuleId = null;
            $this->payDate = $bill->due_date->toDateString();
            $this->payAmount = number_format((float) $bill->amount, 2, '.', '');
        } else {
            $rule = RecurringBill::where('user_id', auth()->id())->findOrFail($id);
            $this->payingRuleId = $id;
            $this->payOneOffId = null;
            $this->payDate = $date;
            $this->payAmount = number_format((float) $rule->amount, 2, '.', '');
        }

        $this->resetValidation();
        $this->showPayModal = true;
    }

    public function closePayModal(): void
    {
        $this->showPayModal = false;
        $this->payingRuleId = null;
        $this->payOneOffId = null;
        $this->resetValidation();
    }

    public function savePayment(): void
    {
        if ($this->payType === 'oneoff') {
            $this->validate([
                'payDate' => 'required|date',
                'payAmount' => 'required|numeric|min:0',
            ]);

            OneOffBill::where('user_id', auth()->id())->findOrFail($this->payOneOffId)->update([
                'is_paid' => true,
                'date_paid' => $this->payDate,
                'amount' => $this->payAmount,
            ]);

            $this->closePayModal();
            $this->dispatch('toast', message: 'Payment recorded.', type: 'success');

            return;
        }

        $this->validate([
            'payDate' => 'required|date',
            'payAmount' => 'required|numeric|min:0',
            'payReference' => 'nullable|string|max:100',
            'payNotes' => 'nullable|string|max:1000',
        ]);

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

    public function viewPaymentDetails(string $type, int $id, ?string $date = null): void
    {
        $this->viewingDetailType = $type;
        $this->viewingDetailId = $id;
        $this->viewingDetailDate = $date ?? '';
        $this->showPaymentDetailModal = true;
    }

    public function closePaymentDetailModal(): void
    {
        $this->showPaymentDetailModal = false;
        $this->viewingDetailId = null;
        $this->viewingDetailDate = '';
    }

    public function openEditOccurrence(int $ruleId, string $date): void
    {
        $rule = RecurringBill::where('user_id', auth()->id())->findOrFail($ruleId);

        $override = RecurringBillOverride::where('recurring_bill_id', $ruleId)
            ->whereDate('occurrence_date', $date)
            ->first();

        $this->editingRuleId = $ruleId;
        $this->editingDate = $date;
        $this->editAmount = number_format((float) ($override->amount ?? $rule->amount), 2, '.', '');
        $this->editCategoryId = (string) ($override->category_id ?? $rule->category_id);
        $this->editAccountId = (string) ($override->account_id ?? $rule->account_id);
        $this->editOriginalAmount = $this->editAmount;
        $this->editOriginalCategoryId = $this->editCategoryId;
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
            'editCategoryId' => 'required|exists:categories,id',
            'editAccountId' => 'required|exists:accounts,id',
        ]);

        $unchanged = number_format((float) $this->editAmount, 2, '.', '') === number_format((float) $this->editOriginalAmount, 2, '.', '')
            && $this->editCategoryId === $this->editOriginalCategoryId
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
            'editCategoryId' => 'required|exists:categories,id',
            'editAccountId' => 'required|exists:accounts,id',
        ]);

        $rule = RecurringBill::where('user_id', auth()->id())->findOrFail($this->editingRuleId);

        if ($scope === 'future') {
            $this->splitRuleForFutureEdit($rule);
        } else {
            $override = $this->findOrNewOverride($rule->id, $this->editingDate);
            $override->fill([
                'is_skipped' => false,
                'amount' => $this->editAmount,
                'category_id' => $this->editCategoryId,
                'account_id' => $this->editAccountId,
            ]);
            $override->save();
        }

        app(RecurringBillService::class)->clearCache(auth()->id(), $this->year, $this->month);
        $this->showEditScopeModal = false;
        $this->editingRuleId = null;
        $this->editingDate = '';
        $this->dispatch('toast', message: 'Bill updated.', type: 'success');
    }

    private function findOrNewOverride(int $ruleId, string $date): RecurringBillOverride
    {
        return RecurringBillOverride::where('recurring_bill_id', $ruleId)
            ->whereDate('occurrence_date', $date)
            ->first() ?? new RecurringBillOverride(['recurring_bill_id' => $ruleId, 'occurrence_date' => $date]);
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
        $override = $this->findOrNewOverride($this->deletingOccurrenceRuleId, $this->deletingOccurrenceDate);
        $override->is_skipped = true;
        $override->save();

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

    // ── Add/Edit Bill modal ─────────────────────────────────────────────────

    public function openAddBillModal(): void
    {
        $this->resetBillForm();
        $this->editingOneOffId = null;
        $this->isRecurring = false;
        $this->billDueDate = $this->selectedDate ?: today()->toDateString();
        $this->billStartDate = $this->selectedDate ?: today()->toDateString();
        $this->showBillModal = true;
    }

    public function openEditOneOff(int $id): void
    {
        $bill = OneOffBill::where('user_id', auth()->id())->findOrFail($id);

        $this->resetBillForm();
        $this->editingOneOffId = $id;
        $this->isRecurring = false;
        $this->billBillerId = (string) $bill->biller_id;
        $this->billerSearch = $bill->biller->name;
        $this->billAmount = number_format((float) $bill->amount, 2, '.', '');
        $this->billDueDate = $bill->due_date->toDateString();
        $this->billCategoryId = (string) $bill->category_id;
        $this->billSubcategoryId = $bill->subcategory_id ? (string) $bill->subcategory_id : '';
        $this->billAccountId = (string) $bill->account_id;
        $this->resetValidation();
        $this->showBillModal = true;
    }

    public function closeBillModal(): void
    {
        $this->showBillModal = false;
        $this->resetBillForm();
    }

    public function selectBiller(int $billerId): void
    {
        $biller = Biller::where('user_id', auth()->id())->findOrFail($billerId);
        $this->billBillerId = (string) $billerId;
        $this->billerSearch = $biller->name;

        if ($this->isRecurring) {
            return;
        }

        $lastOneOff = OneOffBill::where('user_id', auth()->id())
            ->where('biller_id', $billerId)
            ->orderByDesc('due_date')
            ->first();

        if ($lastOneOff) {
            if ($this->billAmount === '') {
                $this->billAmount = number_format((float) $lastOneOff->amount, 2, '.', '');
            }
            if ($this->billCategoryId === '') {
                $this->billCategoryId = (string) $lastOneOff->category_id;
                $this->billSubcategoryId = $lastOneOff->subcategory_id ? (string) $lastOneOff->subcategory_id : '';
            }

            return;
        }

        $activeRecurring = RecurringBill::where('user_id', auth()->id())
            ->where('biller_id', $billerId)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', today()))
            ->latest('start_date')
            ->first();

        if ($activeRecurring) {
            if ($this->billAmount === '') {
                $this->billAmount = number_format((float) $activeRecurring->amount, 2, '.', '');
            }
            if ($this->billCategoryId === '') {
                $this->billCategoryId = (string) $activeRecurring->category_id;
            }
        }
    }

    public function updatedBillCategoryId(): void
    {
        $this->billSubcategoryId = '';
    }

    public function toggleRecurring(): void
    {
        if ($this->isRecurring) {
            $this->billDueDate = $this->billStartDate;
            $this->isRecurring = false;
        } else {
            $this->billStartDate = $this->billDueDate;
            $this->isRecurring = true;
        }
    }

    public function openQuickAddBiller(): void
    {
        $this->quickBillerName = '';
        $this->resetValidation();
        $this->showQuickAddBillerModal = true;
    }

    public function closeQuickAddBiller(): void
    {
        $this->showQuickAddBillerModal = false;
        $this->quickBillerName = '';
        $this->resetValidation();
    }

    public function saveQuickAddBiller(): void
    {
        $this->validate(['quickBillerName' => 'required|string|max:150']);

        $biller = Biller::create([
            'user_id' => auth()->id(),
            'name' => trim($this->quickBillerName),
        ]);

        $this->billBillerId = (string) $biller->id;
        $this->billerSearch = $biller->name;
        $this->closeQuickAddBiller();
        $this->dispatch('biller-quick-added', id: $biller->id, name: $biller->name);
        $this->dispatch('toast', message: 'Biller added.', type: 'success');
    }

    public function setBillEndDateOffset(string $period): void
    {
        $anchor = $this->billStartDate ? Carbon::parse($this->billStartDate) : today();

        $this->billEndDate = match ($period) {
            '3m' => $anchor->copy()->addMonths(3)->toDateString(),
            '6m' => $anchor->copy()->addMonths(6)->toDateString(),
            '1y' => $anchor->copy()->addYear()->toDateString(),
            '2y' => $anchor->copy()->addYears(2)->toDateString(),
            default => $this->billEndDate,
        };
    }

    public function saveBill(): void
    {
        if ($this->isRecurring && ! $this->editingOneOffId) {
            $this->saveRecurringFromModal();

            return;
        }

        $this->validate([
            'billBillerId' => 'required|exists:billers,id',
            'billAmount' => 'required|numeric|min:0.01',
            'billDueDate' => 'required|date',
            'billCategoryId' => 'required|exists:categories,id',
            'billSubcategoryId' => 'nullable|exists:subcategories,id',
            'billAccountId' => 'required|exists:accounts,id',
        ]);

        $data = [
            'user_id' => auth()->id(),
            'biller_id' => $this->billBillerId,
            'category_id' => $this->billCategoryId,
            'subcategory_id' => $this->billSubcategoryId ?: null,
            'account_id' => $this->billAccountId,
            'amount' => $this->billAmount,
            'due_date' => $this->billDueDate,
        ];

        if ($this->editingOneOffId) {
            OneOffBill::where('user_id', auth()->id())->findOrFail($this->editingOneOffId)->update($data);
            $this->closeBillModal();
            $this->dispatch('toast', message: 'Bill updated.', type: 'success');

            return;
        }

        OneOffBill::create($data);
        $this->closeBillModal();
        $this->dispatch('toast', message: 'Bill added.', type: 'success');

        $count = OneOffBill::where('user_id', auth()->id())->where('biller_id', $data['biller_id'])->count();
        if ($count === 2) {
            $biller = Biller::find($data['biller_id']);
            $this->promptBillerId = $biller->id;
            $this->promptBillerName = $biller->name;
            $this->showRecurringPromptModal = true;
        }
    }

    private function saveRecurringFromModal(): void
    {
        $this->validate([
            'billBillerId' => 'required|exists:billers,id',
            'billFrequencyId' => 'required|exists:frequencies,id',
            'billCategoryId' => 'required|exists:categories,id',
            'billSubcategoryId' => 'nullable|exists:subcategories,id',
            'billAccountId' => 'required|exists:accounts,id',
            'billAmount' => 'required|numeric|min:0.01',
            'billStartDate' => 'required|date',
            'billEndDate' => 'nullable|date|after_or_equal:billStartDate',
        ]);

        RecurringBill::create([
            'user_id' => auth()->id(),
            'biller_id' => $this->billBillerId,
            'frequency_id' => $this->billFrequencyId,
            'category_id' => $this->billCategoryId,
            'subcategory_id' => $this->billSubcategoryId ?: null,
            'account_id' => $this->billAccountId,
            'amount' => $this->billAmount,
            'start_date' => $this->billStartDate,
            'end_date' => $this->billEndDate ?: null,
        ]);

        app(RecurringBillService::class)->clearCache(auth()->id(), $this->year, $this->month);
        $this->closeBillModal();
        $this->dispatch('toast', message: 'Recurring bill created.', type: 'success');
    }

    public function acceptRecurringPrompt(): void
    {
        $last = OneOffBill::where('user_id', auth()->id())
            ->where('biller_id', $this->promptBillerId)
            ->orderByDesc('due_date')
            ->first();

        $this->resetBillForm();
        $this->isRecurring = true;
        $this->editingOneOffId = null;
        $this->billBillerId = (string) $this->promptBillerId;
        $this->billerSearch = $this->promptBillerName;
        $this->billAmount = $last ? number_format((float) $last->amount, 2, '.', '') : '';
        $this->billCategoryId = $last ? (string) $last->category_id : '';
        $this->billSubcategoryId = $last && $last->subcategory_id ? (string) $last->subcategory_id : '';
        $this->billAccountId = $last ? (string) $last->account_id : '';
        $this->billStartDate = today()->toDateString();
        $this->showRecurringPromptModal = false;
        $this->promptBillerId = null;
        $this->promptBillerName = '';
        $this->showBillModal = true;
    }

    public function dismissRecurringPrompt(): void
    {
        $this->showRecurringPromptModal = false;
        $this->promptBillerId = null;
        $this->promptBillerName = '';
    }

    public function switchToDeleteOneOff(): void
    {
        $id = $this->editingOneOffId;
        $this->closeBillModal();
        $this->confirmDeleteOneOff($id);
    }

    public function confirmDeleteOneOff(int $id): void
    {
        OneOffBill::where('user_id', auth()->id())->findOrFail($id);
        $this->deletingOneOffId = $id;
        $this->showDeleteOneOffModal = true;
    }

    public function deleteOneOff(): void
    {
        OneOffBill::where('user_id', auth()->id())->findOrFail($this->deletingOneOffId)->delete();
        $this->cancelDeleteOneOff();
        $this->dispatch('toast', message: 'Bill deleted.', type: 'success');
    }

    public function cancelDeleteOneOff(): void
    {
        $this->showDeleteOneOffModal = false;
        $this->deletingOneOffId = null;
    }

    private function resetBillForm(): void
    {
        $this->billBillerId = '';
        $this->billerSearch = '';
        $this->billAmount = '';
        $this->billDueDate = '';
        $this->billCategoryId = '';
        $this->billSubcategoryId = '';
        $this->billAccountId = '';
        $this->billFrequencyId = '';
        $this->billStartDate = '';
        $this->billEndDate = '';
        $this->isRecurring = false;
        $this->resetValidation();
    }

    public function render()
    {
        $user = auth()->user();
        $service = app(RecurringBillService::class);
        $instances = $service->getForMonth($user->id, $this->year, $this->month);
        $today = now()->toDateString();

        $oneOffBills = OneOffBill::with(['biller', 'category', 'subcategory', 'account'])
            ->where('user_id', $user->id)
            ->whereYear('due_date', $this->year)
            ->whereMonth('due_date', $this->month)
            ->get();

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
        foreach ($oneOffBills as $bill) {
            $key = $bill->due_date->toDateString();
            $dayStatus[$key] ??= ['all_paid' => true, 'count' => 0];
            $dayStatus[$key]['count']++;
            if (! $bill->is_paid) {
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

        // ── Filtered one-off bill list (mirrors $listInstances filtering) ─
        $listOneOffBills = $oneOffBills;

        if ($this->selectedDate) {
            $listOneOffBills = $listOneOffBills->filter(
                fn ($b) => $b->due_date->toDateString() === $this->selectedDate
            );
        } else {
            if ($this->search) {
                $s = strtolower($this->search);
                $listOneOffBills = $listOneOffBills->filter(
                    fn ($b) => str_contains(strtolower($b->biller->name), $s)
                );
            }
            if ($this->tab === 'paid') {
                $listOneOffBills = $listOneOffBills->filter(fn ($b) => $b->is_paid);
            } elseif ($this->tab === 'unpaid') {
                $listOneOffBills = $listOneOffBills->filter(fn ($b) => ! $b->is_paid);
            }
        }

        $listOneOffBills = $listOneOffBills->sortBy('due_date')->values();

        // ── Month totals (always full month, unaffected by day selection) ─
        $paidTotal = collect($instances)->filter(fn ($b) => $b->isPaid)->sum(fn ($b) => $b->getAmount())
            + $oneOffBills->where('is_paid', true)->sum(fn ($b) => (float) $b->amount);
        $unpaidTotal = collect($instances)->filter(fn ($b) => ! $b->isPaid)->sum(fn ($b) => $b->getAmount())
            + $oneOffBills->where('is_paid', false)->sum(fn ($b) => (float) $b->amount);
        $periodLabel = $firstOfMonth->format('F Y');

        $categories = Category::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get();
        $accounts = Account::where('user_id', $user->id)->orderBy('name')->get();
        $billers = Biller::where('user_id', $user->id)->orderBy('name')->get();
        $frequencies = Frequency::orderBy('id')->get();

        $viewingPaymentDetail = null;
        if ($this->showPaymentDetailModal && $this->viewingDetailId) {
            $viewingPaymentDetail = $this->viewingDetailType === 'oneoff'
                ? OneOffBill::with(['biller', 'category', 'account'])->find($this->viewingDetailId)
                : Payment::with(['biller', 'category', 'account'])
                    ->where('recurring_bill_id', $this->viewingDetailId)
                    ->whereDate('recurring_bill_date', $this->viewingDetailDate)
                    ->first();
        }

        return view('livewire.bills-index', compact(
            'instances', 'listInstances', 'listOneOffBills', 'dayStatus', 'today',
            'weekDays', 'calDays', 'paidTotal', 'unpaidTotal', 'periodLabel',
            'categories', 'subcategories', 'accounts', 'billers', 'frequencies',
            'viewingPaymentDetail'
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
