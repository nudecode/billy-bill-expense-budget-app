<?php
namespace App\Livewire;
use App\Models\Payment;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Payments — Billy')]
class PaymentsIndex extends Component
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
        $payments = Payment::with(['biller', 'category', 'account'])
            ->where('user_id', auth()->id())
            ->whereYear('payment_date', $this->year)
            ->whereMonth('payment_date', $this->month)
            ->orderBy('payment_date')
            ->get();

        $total = $payments->sum('amount');
        $periodLabel = Carbon::create($this->year, $this->month, 1)->format('F Y');

        return view('livewire.payments-index', compact('payments', 'total', 'periodLabel'))->layout('layouts.app', ['title' => 'Payments']);
    }
}
