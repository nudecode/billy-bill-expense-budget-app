<?php

namespace App\Livewire;

use App\Models\OneOffBill;
use App\Models\Payment;
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
    #[Url(as: 'y')] public int $year;
    #[Url(as: 'm')] public int $month;
    #[Url] public string $calView = 'week';   // 'week' | 'month'
    #[Url] public string $tab = 'all';
    #[Url] public string $search = '';
    #[Url] public string $selectedDate = '';  // Y-m-d, empty = show all

    public function mount(): void
    {
        $this->year  ??= now()->year;
        $this->month ??= now()->month;
        if (!$this->selectedDate) {
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
        if ($this->month === 1) { $this->month = 12; $this->year--; }
        else { $this->month--; }
    }

    public function nextMonth(): void
    {
        $this->selectedDate = '';
        if ($this->month === 12) { $this->month = 1; $this->year++; }
        else { $this->month++; }
    }

    public function previousWeek(): void
    {
        $anchor = $this->resolveWeekAnchor();
        $date   = Carbon::parse($anchor)->subWeek();
        $this->selectedDate = $date->toDateString();
        $this->year  = $date->year;
        $this->month = $date->month;
    }

    public function nextWeek(): void
    {
        $anchor = $this->resolveWeekAnchor();
        $date   = Carbon::parse($anchor)->addWeek();
        $this->selectedDate = $date->toDateString();
        $this->year  = $date->year;
        $this->month = $date->month;
    }

    public function markPaid(int $ruleId, string $date): void
    {
        $user = auth()->user();
        $rule = \App\Models\RecurringBill::findOrFail($ruleId);

        $alreadyPaid = Payment::where('user_id', $user->id)
            ->where('recurring_bill_id', $ruleId)
            ->where('recurring_bill_date', $date)
            ->exists();

        if (!$alreadyPaid) {
            Payment::create([
                'user_id'             => $user->id,
                'recurring_bill_id'   => $ruleId,
                'recurring_bill_date' => $date,
                'biller_id'           => $rule->biller_id,
                'account_id'          => $rule->account_id,
                'category_id'         => $rule->category_id,
                'amount'              => $rule->amount,
                'payment_date'        => today()->toDateString(),
            ]);
        }

        app(RecurringBillService::class)->clearCache($user->id, $this->year, $this->month);
    }

    public function render()
    {
        $user      = auth()->user();
        $service   = app(RecurringBillService::class);
        $instances = $service->getForMonth($user->id, $this->year, $this->month);
        $today     = now()->toDateString();

        // Build per-day status lookup for calendar indicators
        $dayStatus = [];
        foreach ($instances as $inst) {
            $key = $inst->date->toDateString();
            $dayStatus[$key] ??= ['all_paid' => true, 'count' => 0];
            $dayStatus[$key]['count']++;
            if (!$inst->isPaid) {
                $dayStatus[$key]['all_paid'] = false;
            }
        }

        // ── Week strip data ────────────────────────────────────────────
        $weekAnchor = $this->resolveWeekAnchor();
        $weekStart  = Carbon::parse($weekAnchor)->startOfWeek(Carbon::MONDAY);
        $weekDays   = collect(range(0, 6))
            ->map(fn ($i) => $weekStart->copy()->addDays($i)->toDateString());

        // ── Month grid data (6 rows × 7 cols) ─────────────────────────
        $firstOfMonth = Carbon::create($this->year, $this->month, 1);
        $gridStart    = $firstOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calDays      = collect(range(0, 41))
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
                $listInstances = $listInstances->filter(fn ($b) => !$b->isPaid);
            }
        }

        $listInstances = $listInstances->values()->all();

        // ── Month totals (always full month, unaffected by day selection) ─
        $paidTotal   = collect($instances)->filter(fn ($b) => $b->isPaid)->sum(fn ($b) => $b->getAmount());
        $unpaidTotal = collect($instances)->filter(fn ($b) => !$b->isPaid)->sum(fn ($b) => $b->getAmount());
        $periodLabel = $firstOfMonth->format('F Y');

        return view('livewire.bills-index', compact(
            'instances', 'listInstances', 'dayStatus', 'today',
            'weekDays', 'calDays', 'paidTotal', 'unpaidTotal', 'periodLabel'
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
