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
            ->withSum('payments', 'amount')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        // Group by first letter only when not searching (grouping while searching is confusing)
        $grouped = $this->search
            ? null
            : $billers->groupBy(fn ($b) => strtoupper(substr($b->name, 0, 1)));

        $presentLetters = $grouped ? $grouped->keys()->all() : [];

        return view('livewire.billers-index', compact('billers', 'grouped', 'presentLetters'))
            ->layout('layouts.app', ['title' => 'Billers']);
    }
}
