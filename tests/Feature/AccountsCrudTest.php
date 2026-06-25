<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountsCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_accounts_page_loads(): void
    {
        $this->actingAs($this->user)->get('/accounts')->assertOk();
    }

    public function test_can_create_account(): void
    {
        $this->actingAs($this->user);

        $component = new \App\Livewire\AccountsIndex();
        $component->name = 'My Savings';
        app()->call([$component, 'save']);

        $this->assertDatabaseHas('accounts', [
            'user_id' => $this->user->id,
            'name'    => 'My Savings',
        ]);
    }

    public function test_create_account_requires_name(): void
    {
        $this->actingAs($this->user);

        $component = new \App\Livewire\AccountsIndex();
        $component->name = '';

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app()->call([$component, 'save']);
    }

    public function test_can_edit_account(): void
    {
        $account = Account::factory()->create(['user_id' => $this->user->id, 'name' => 'Old Name']);
        $this->actingAs($this->user);

        $component = new \App\Livewire\AccountsIndex();
        app()->call([$component, 'openEdit'], ['id' => $account->id]);
        $component->name = 'New Name';
        app()->call([$component, 'save']);

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'name' => 'New Name']);
    }

    public function test_user_cannot_edit_another_users_account(): void
    {
        $other   = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $other->id]);

        $this->actingAs($this->user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $component = new \App\Livewire\AccountsIndex();
        app()->call([$component, 'openEdit'], ['id' => $account->id]);
    }

    public function test_can_delete_account(): void
    {
        $account = Account::factory()->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user);

        $component = new \App\Livewire\AccountsIndex();
        app()->call([$component, 'confirmDelete'], ['id' => $account->id]);
        $this->assertEmpty($component->deleteBlockReason);

        app()->call([$component, 'deleteAccount']);
        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }

    public function test_delete_blocked_when_account_has_linked_bills(): void
    {
        $account = Account::factory()->create(['user_id' => $this->user->id]);
        \App\Models\RecurringBill::factory()->create([
            'user_id'    => $this->user->id,
            'account_id' => $account->id,
        ]);

        $this->actingAs($this->user);

        $component = new \App\Livewire\AccountsIndex();
        app()->call([$component, 'confirmDelete'], ['id' => $account->id]);

        $this->assertNotEmpty($component->deleteBlockReason);
        $this->assertDatabaseHas('accounts', ['id' => $account->id]);
    }
}
