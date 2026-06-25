<?php
namespace App\Livewire;
use App\Models\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Accounts — Billy')]
class AccountsIndex extends Component
{
    public function render()
    {
        $accounts = Account::where('user_id', auth()->id())->orderBy('name')->get();
        return view('livewire.accounts-index', compact('accounts'))->layout('layouts.app', ['title' => 'Accounts']);
    }
}
