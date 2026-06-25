<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Biller;
use App\Models\Category;
use App\Models\Frequency;
use App\Models\Payment;
use App\Models\RecurringBill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillersCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_billers_page_loads(): void
    {
        $this->actingAs($this->user)->get('/billers')->assertOk();
    }

    public function test_can_create_biller(): void
    {
        $this->actingAs($this->user);

        $component = new \App\Livewire\BillersIndex();
        $component->name          = 'Test Biller';
        $component->phone         = '1300 000 000';
        $component->email         = '';
        $component->accountNumber = '';
        app()->call([$component, 'save']);

        $this->assertDatabaseHas('billers', [
            'user_id' => $this->user->id,
            'name'    => 'Test Biller',
            'phone'   => '1300 000 000',
        ]);
    }

    public function test_create_biller_requires_name(): void
    {
        $this->actingAs($this->user)->get('/billers')->assertOk();

        // Simulate missing name via direct unit test of component
        $component = new \App\Livewire\BillersIndex();
        $component->name = '';
        $component->phone = '';
        $component->email = '';
        $component->accountNumber = '';

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app()->call([$component, 'save']);
    }

    public function test_user_cannot_edit_another_users_biller(): void
    {
        $other  = User::factory()->create();
        $biller = Biller::factory()->create(['user_id' => $other->id]);

        $this->actingAs($this->user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $component = new \App\Livewire\BillersIndex();
        app()->call([$component, 'openEdit'], ['id' => $biller->id]);
    }

    public function test_delete_blocked_when_has_active_recurring_bills(): void
    {
        $biller   = Biller::factory()->create(['user_id' => $this->user->id]);
        $account  = Account::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create();
        $freq     = Frequency::factory()->create();

        RecurringBill::factory()->create([
            'user_id'    => $this->user->id,
            'biller_id'  => $biller->id,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'frequency_id' => $freq->id,
            'start_date' => now()->subMonth(),
            'end_date'   => now()->addYear(),
        ]);

        $this->actingAs($this->user);

        $component = new \App\Livewire\BillersIndex();
        app()->call([$component, 'confirmDelete'], ['id' => $biller->id]);

        $this->assertNotEmpty($component->deleteBlockReason);
        $this->assertDatabaseHas('billers', ['id' => $biller->id]);
    }

    public function test_can_delete_biller_with_no_active_bills(): void
    {
        $biller = Biller::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user);

        $component = new \App\Livewire\BillersIndex();
        app()->call([$component, 'confirmDelete'], ['id' => $biller->id]);
        $this->assertEmpty($component->deleteBlockReason);

        app()->call([$component, 'deleteBiller']);
        $this->assertDatabaseMissing('billers', ['id' => $biller->id]);
    }

    private function livewirePayload(string $component, string $method, array $updates): array
    {
        return []; // Placeholder — direct component testing used instead
    }
}
