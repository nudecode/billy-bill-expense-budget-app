<?php
namespace App\Livewire;
use App\Models\RecurringBill;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Recurring Bills — Billy')]
class RecurringBillsIndex extends Component
{
    #[Url] public string $search = '';

    public function render()
    {
        $rules = RecurringBill::with(['biller', 'frequency', 'category', 'account'])
            ->where('user_id', auth()->id())
            ->when($this->search, fn ($q) => $q->whereHas('biller', fn ($b) => $b->where('name', 'like', "%{$this->search}%")))
            ->orderBy('start_date')
            ->get();

        return view('livewire.recurring-bills-index', compact('rules'))->layout('layouts.app', ['title' => 'Recurring Bills']);
    }
}
