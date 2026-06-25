<?php

namespace App\Livewire;

use App\Models\Biller;
use App\Models\OneOffBill;
use App\Models\OneOffIncome;
use App\Services\RecurringBillService;
use App\Services\RecurringIncomeService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard — Billy')]
class Dashboard extends Component
{
    #[Url(as: 'y')]
    public int $year;

    #[Url(as: 'm')]
    public int $month;

    public function mount(): void
    {
        $this->year  ??= now()->year;
        $this->month ??= now()->month;
    }

    public function previousMonth(): void
    {
        if ($this->month === 1) { $this->month = 12; $this->year--; }
        else { $this->month--; }
    }

    public function nextMonth(): void
    {
        if ($this->month === 12) { $this->month = 1; $this->year++; }
        else { $this->month++; }
    }

    public function render()
    {
        $user  = auth()->user();
        $uid   = $user->id;

        $billService   = app(RecurringBillService::class);
        $incomeService = app(RecurringIncomeService::class);

        $billInstances   = $billService->getForMonth($uid, $this->year, $this->month);
        $incomeInstances = $incomeService->getForMonth($uid, $this->year, $this->month);

        $oneOffBills  = OneOffBill::with(['biller', 'category'])
            ->where('user_id', $uid)
            ->whereYear('due_date', $this->year)
            ->whereMonth('due_date', $this->month)
            ->get();

        $oneOffIncome = OneOffIncome::where('user_id', $uid)
            ->whereYear('income_date', $this->year)
            ->whereMonth('income_date', $this->month)
            ->get();

        // Stats
        $recurringTotal  = collect($billInstances)->sum(fn ($b) => $b->getAmount());
        $oneOffTotal     = $oneOffBills->sum('amount');
        $totalBills      = $recurringTotal + $oneOffTotal;

        $paidInstances   = collect($billInstances)->filter(fn ($b) => $b->isPaid);
        $unpaidInstances = collect($billInstances)->filter(fn ($b) => !$b->isPaid);
        $paidOneOff      = $oneOffBills->where('is_paid', true);
        $unpaidOneOff    = $oneOffBills->where('is_paid', false);

        $paidCount   = $paidInstances->count() + $paidOneOff->count();
        $unpaidCount = $unpaidInstances->count() + $unpaidOneOff->count();
        $paidAmount  = $paidInstances->sum(fn ($b) => $b->getAmount()) + $paidOneOff->sum('amount');
        $unpaidAmount = $unpaidInstances->sum(fn ($b) => $b->getAmount()) + $unpaidOneOff->sum('amount');

        $totalIncome = collect($incomeInstances)->sum(fn ($i) => $i->getAmount()) + $oneOffIncome->sum('amount');
        $netAmount   = $totalIncome - $totalBills;

        $billerCount = Biller::where('user_id', $uid)->count();

        // Upcoming bills (next 7 days from today, only if viewing current month)
        $today    = now()->startOfDay();
        $upcoming = [];
        if ($this->year === $today->year && $this->month === $today->month) {
            $cutoff   = $today->copy()->addDays(7);
            $upcoming = collect($billInstances)
                ->filter(fn ($b) => !$b->isPaid && $b->date->between($today, $cutoff))
                ->take(5)
                ->values()
                ->all();
        }

        $periodLabel = Carbon::create($this->year, $this->month, 1)->format('F Y');

        return view('livewire.dashboard', compact(
            'totalBills', 'totalIncome', 'netAmount',
            'paidCount', 'unpaidCount', 'paidAmount', 'unpaidAmount',
            'billerCount', 'upcoming', 'periodLabel',
            'recurringTotal', 'oneOffTotal',
        ))->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
