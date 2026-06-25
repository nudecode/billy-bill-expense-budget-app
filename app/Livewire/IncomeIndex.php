<?php
namespace App\Livewire;
use App\Models\OneOffIncome;
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
    #[Url(as: 'y')] public int $year;
    #[Url(as: 'm')] public int $month;

    public function mount(): void
    {
        $this->year  ??= now()->year;
        $this->month ??= now()->month;
    }

    public function previousMonth(): void
    {
        if ($this->month === 1) { $this->month = 12; $this->year--; } else { $this->month--; }
    }

    public function nextMonth(): void
    {
        if ($this->month === 12) { $this->month = 1; $this->year++; } else { $this->month++; }
    }

    public function render()
    {
        $user      = auth()->user();
        $service   = app(RecurringIncomeService::class);
        $instances = $service->getForMonth($user->id, $this->year, $this->month);

        $oneOff = OneOffIncome::with('account')
            ->where('user_id', $user->id)
            ->whereYear('income_date', $this->year)
            ->whereMonth('income_date', $this->month)
            ->get();

        $total = collect($instances)->sum(fn ($i) => $i->getAmount()) + $oneOff->sum('amount');
        $periodLabel = Carbon::create($this->year, $this->month, 1)->format('F Y');

        return view('livewire.income-index', compact('instances', 'oneOff', 'total', 'periodLabel'))->layout('layouts.app', ['title' => 'Income']);
    }
}
