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
    #[Url] public string $tab = 'all';
    #[Url] public string $search = '';

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

    public function markPaid(int $ruleId, string $date): void
    {
        $user = auth()->user();
        $rule = \App\Models\RecurringBill::findOrFail($ruleId);
        $billDate = Carbon::parse($date);

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
        $this->dispatch('bill-paid');
    }

    public function render()
    {
        $user     = auth()->user();
        $service  = app(RecurringBillService::class);
        $instances = $service->getForMonth($user->id, $this->year, $this->month);

        $oneOffBills = OneOffBill::with(['biller', 'category'])
            ->where('user_id', $user->id)
            ->whereYear('due_date', $this->year)
            ->whereMonth('due_date', $this->month)
            ->get();

        if ($this->search) {
            $s = strtolower($this->search);
            $instances   = collect($instances)->filter(fn ($b) => str_contains(strtolower($b->getBillerName()), $s))->values()->all();
            $oneOffBills = $oneOffBills->filter(fn ($b) => str_contains(strtolower($b->biller->name), $s));
        }

        $paidTotal   = collect($instances)->filter(fn ($b) => $b->isPaid)->sum(fn ($b) => $b->getAmount())
                     + $oneOffBills->where('is_paid', true)->sum('amount');
        $unpaidTotal = collect($instances)->filter(fn ($b) => !$b->isPaid)->sum(fn ($b) => $b->getAmount())
                     + $oneOffBills->where('is_paid', false)->sum('amount');

        $periodLabel = Carbon::create($this->year, $this->month, 1)->format('F Y');

        return view('livewire.bills-index', compact('instances', 'oneOffBills', 'paidTotal', 'unpaidTotal', 'periodLabel'))->layout('layouts.app', ['title' => 'Bills']);
    }
}
