<?php
namespace App\Livewire;
use App\Models\Biller;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Billers — Billy')]
class BillersIndex extends Component
{
    #[Url] public string $search = '';

    public function render()
    {
        $billers = Biller::where('user_id', auth()->id())
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        return view('livewire.billers-index', compact('billers'))->layout('layouts.app', ['title' => 'Billers']);
    }
}
