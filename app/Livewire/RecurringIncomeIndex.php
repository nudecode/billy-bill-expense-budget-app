<?php
namespace App\Livewire;
use App\Models\RecurringIncome;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Recurring Income — Billy')]
class RecurringIncomeIndex extends Component
{
    public function render()
    {
        $rules = RecurringIncome::with(['frequency', 'account'])
            ->where('user_id', auth()->id())
            ->orderBy('start_date')
            ->get();

        return view('livewire.recurring-income-index', compact('rules'))->layout('layouts.app', ['title' => 'Recurring Income']);
    }
}
