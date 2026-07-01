<?php

namespace Tests\Feature;

use App\Livewire\BillsIndex;
use App\Livewire\IncomeIndex;
use App\Models\Account;
use App\Models\Biller;
use App\Models\Category;
use App\Models\Frequency;
use App\Models\OneOffBill;
use App\Models\OneOffIncome;
use App\Models\RecurringBill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OneOffCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Biller $biller;

    private Account $account;

    private Category $category;

    private Frequency $frequency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->biller = Biller::factory()->create(['user_id' => $this->user->id]);
        $this->account = Account::factory()->create(['user_id' => $this->user->id]);
        $this->category = Category::factory()->create();
        $this->frequency = Frequency::factory()->create();
    }

    private function billComponent(): BillsIndex
    {
        $component = new BillsIndex;
        $component->year = now()->year;
        $component->month = now()->month;

        return $component;
    }

    private function incomeComponent(): IncomeIndex
    {
        $component = new IncomeIndex;
        $component->year = now()->year;
        $component->month = now()->month;

        return $component;
    }

    public function test_can_create_one_off_bill(): void
    {
        $this->actingAs($this->user);

        $component = $this->billComponent();
        $component->isRecurring = false;
        $component->billBillerId = (string) $this->biller->id;
        $component->billAmount = '49.95';
        $component->billDueDate = today()->toDateString();
        $component->billCategoryId = (string) $this->category->id;
        $component->billAccountId = (string) $this->account->id;
        app()->call([$component, 'saveBill']);

        $this->assertDatabaseHas('one_off_bills', [
            'user_id' => $this->user->id,
            'biller_id' => $this->biller->id,
            'amount' => '49.95',
        ]);
    }

    public function test_can_create_recurring_bill_via_same_modal(): void
    {
        $this->actingAs($this->user);

        $component = $this->billComponent();
        $component->isRecurring = true;
        $component->billBillerId = (string) $this->biller->id;
        $component->billFrequencyId = (string) $this->frequency->id;
        $component->billCategoryId = (string) $this->category->id;
        $component->billAccountId = (string) $this->account->id;
        $component->billAmount = '100.00';
        $component->billStartDate = today()->toDateString();
        app()->call([$component, 'saveBill']);

        $this->assertDatabaseHas('recurring_bills', [
            'user_id' => $this->user->id,
            'biller_id' => $this->biller->id,
            'amount' => '100.00',
        ]);
        $this->assertDatabaseMissing('one_off_bills', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_edit_one_off_bill(): void
    {
        $bill = OneOffBill::create([
            'user_id' => $this->user->id,
            'biller_id' => $this->biller->id,
            'category_id' => $this->category->id,
            'account_id' => $this->account->id,
            'amount' => 20,
            'due_date' => today(),
        ]);

        $this->actingAs($this->user);

        $component = $this->billComponent();
        app()->call([$component, 'openEditOneOff'], ['id' => $bill->id]);
        $component->billAmount = '30.00';
        app()->call([$component, 'saveBill']);

        $this->assertDatabaseHas('one_off_bills', [
            'id' => $bill->id,
            'amount' => '30.00',
        ]);
    }

    public function test_can_delete_one_off_bill(): void
    {
        $bill = OneOffBill::create([
            'user_id' => $this->user->id,
            'biller_id' => $this->biller->id,
            'category_id' => $this->category->id,
            'account_id' => $this->account->id,
            'amount' => 20,
            'due_date' => today(),
        ]);

        $this->actingAs($this->user);

        $component = $this->billComponent();
        app()->call([$component, 'confirmDeleteOneOff'], ['id' => $bill->id]);
        app()->call([$component, 'deleteOneOff']);

        $this->assertDatabaseMissing('one_off_bills', ['id' => $bill->id]);
    }

    public function test_recurring_prompt_fires_on_second_manual_entry_for_same_biller(): void
    {
        $this->actingAs($this->user);

        $component = $this->billComponent();
        $component->billBillerId = (string) $this->biller->id;
        $component->billAmount = '15.00';
        $component->billDueDate = today()->toDateString();
        $component->billCategoryId = (string) $this->category->id;
        $component->billAccountId = (string) $this->account->id;
        app()->call([$component, 'saveBill']);

        $this->assertFalse($component->showRecurringPromptModal);

        $component->billBillerId = (string) $this->biller->id;
        $component->billAmount = '15.00';
        $component->billDueDate = today()->addDay()->toDateString();
        $component->billCategoryId = (string) $this->category->id;
        $component->billAccountId = (string) $this->account->id;
        app()->call([$component, 'saveBill']);

        $this->assertTrue($component->showRecurringPromptModal);
        $this->assertSame($this->biller->id, $component->promptBillerId);

        // Simulate a fresh page load before the 3rd entry — prompt shouldn't re-fire at count 3.
        $component->showRecurringPromptModal = false;
        $component->billBillerId = (string) $this->biller->id;
        $component->billAmount = '15.00';
        $component->billDueDate = today()->addDays(2)->toDateString();
        $component->billCategoryId = (string) $this->category->id;
        $component->billAccountId = (string) $this->account->id;
        app()->call([$component, 'saveBill']);

        $this->assertFalse($component->showRecurringPromptModal);
    }

    public function test_can_pay_one_off_bill_without_creating_payment_row(): void
    {
        $bill = OneOffBill::create([
            'user_id' => $this->user->id,
            'biller_id' => $this->biller->id,
            'category_id' => $this->category->id,
            'account_id' => $this->account->id,
            'amount' => 20,
            'due_date' => today(),
        ]);

        $this->actingAs($this->user);

        $component = $this->billComponent();
        app()->call([$component, 'openPayModal'], ['type' => 'oneoff', 'id' => $bill->id]);
        app()->call([$component, 'savePayment']);

        $bill->refresh();
        $this->assertTrue($bill->is_paid);
        $this->assertNotNull($bill->date_paid);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_paying_recurring_occurrence_still_creates_payment_row(): void
    {
        $rule = RecurringBill::factory()->create([
            'user_id' => $this->user->id,
            'biller_id' => $this->biller->id,
            'category_id' => $this->category->id,
            'account_id' => $this->account->id,
            'frequency_id' => $this->frequency->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
        ]);

        $this->actingAs($this->user);

        $component = $this->billComponent();
        $date = today()->toDateString();
        app()->call([$component, 'openPayModal'], ['type' => 'recurring', 'id' => $rule->id, 'date' => $date]);
        app()->call([$component, 'savePayment']);

        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'recurring_bill_id' => $rule->id,
        ]);
    }

    public function test_can_create_edit_and_delete_one_off_income(): void
    {
        $this->actingAs($this->user);

        $component = $this->incomeComponent();
        $component->isRecurringIncome = false;
        $component->incomeName = 'Freelance job';
        $component->incomeAmount = '250.00';
        $component->incomeDate = today()->toDateString();
        $component->incomeAccountId = (string) $this->account->id;
        app()->call([$component, 'saveIncome']);

        $income = OneOffIncome::where('user_id', $this->user->id)->firstOrFail();
        $this->assertSame('Freelance job', $income->name);

        $component2 = $this->incomeComponent();
        app()->call([$component2, 'openEditOneOffIncome'], ['id' => $income->id]);
        $component2->incomeAmount = '300.00';
        app()->call([$component2, 'saveIncome']);

        $this->assertDatabaseHas('one_off_income', [
            'id' => $income->id,
            'amount' => '300.00',
        ]);

        $component3 = $this->incomeComponent();
        app()->call([$component3, 'confirmDeleteOneOffIncome'], ['id' => $income->id]);
        app()->call([$component3, 'deleteOneOffIncome']);

        $this->assertDatabaseMissing('one_off_income', ['id' => $income->id]);
    }

    public function test_viewing_payment_details_for_paid_one_off_bill(): void
    {
        $bill = OneOffBill::create([
            'user_id' => $this->user->id,
            'biller_id' => $this->biller->id,
            'category_id' => $this->category->id,
            'account_id' => $this->account->id,
            'amount' => 20,
            'due_date' => today(),
            'is_paid' => true,
            'date_paid' => today(),
        ]);

        $this->actingAs($this->user);

        $component = $this->billComponent();
        app()->call([$component, 'viewPaymentDetails'], ['type' => 'oneoff', 'id' => $bill->id]);

        $this->assertTrue($component->showPaymentDetailModal);

        $view = app()->call([$component, 'render']);
        $this->assertSame($bill->id, $view->getData()['viewingPaymentDetail']->id);
    }

    public function test_viewing_payment_details_for_paid_recurring_occurrence(): void
    {
        $rule = RecurringBill::factory()->create([
            'user_id' => $this->user->id,
            'biller_id' => $this->biller->id,
            'category_id' => $this->category->id,
            'account_id' => $this->account->id,
            'frequency_id' => $this->frequency->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
        ]);

        $this->actingAs($this->user);
        $date = today()->toDateString();

        $payer = $this->billComponent();
        app()->call([$payer, 'openPayModal'], ['type' => 'recurring', 'id' => $rule->id, 'date' => $date]);
        app()->call([$payer, 'savePayment']);

        $component = $this->billComponent();
        app()->call([$component, 'viewPaymentDetails'], ['type' => 'recurring', 'id' => $rule->id, 'date' => $date]);

        $view = app()->call([$component, 'render']);
        $this->assertSame($rule->id, $view->getData()['viewingPaymentDetail']->recurring_bill_id);
    }

    public function test_select_biller_does_not_overwrite_already_chosen_category(): void
    {
        $otherCategory = Category::factory()->create();

        OneOffBill::create([
            'user_id' => $this->user->id,
            'biller_id' => $this->biller->id,
            'category_id' => $otherCategory->id,
            'account_id' => $this->account->id,
            'amount' => 20,
            'due_date' => today(),
        ]);

        $this->actingAs($this->user);

        $component = $this->billComponent();
        app()->call([$component, 'openAddBillModal']);
        $component->billCategoryId = (string) $this->category->id;
        app()->call([$component, 'selectBiller'], ['billerId' => $this->biller->id]);

        $this->assertSame((string) $this->category->id, $component->billCategoryId);
    }

    public function test_quick_add_biller_creates_and_selects_it(): void
    {
        $this->actingAs($this->user);

        $component = $this->billComponent();
        app()->call([$component, 'openQuickAddBiller']);
        $component->quickBillerName = 'Origin Energy';
        app()->call([$component, 'saveQuickAddBiller']);

        $this->assertDatabaseHas('billers', [
            'user_id' => $this->user->id,
            'name' => 'Origin Energy',
        ]);
        $this->assertFalse($component->showQuickAddBillerModal);
        $this->assertSame('Origin Energy', $component->billerSearch);
        $this->assertNotSame('', $component->billBillerId);
    }

    public function test_can_create_recurring_income_via_same_modal(): void
    {
        $this->actingAs($this->user);

        $component = $this->incomeComponent();
        $component->isRecurringIncome = true;
        $component->incomeName = 'Salary';
        $component->incomeFrequencyId = (string) $this->frequency->id;
        $component->incomeAccountId = (string) $this->account->id;
        $component->incomeAmount = '2000.00';
        $component->incomeStartDate = today()->toDateString();
        app()->call([$component, 'saveIncome']);

        $this->assertDatabaseHas('recurring_income', [
            'user_id' => $this->user->id,
            'name' => 'Salary',
            'amount' => '2000.00',
        ]);
        $this->assertDatabaseMissing('one_off_income', [
            'user_id' => $this->user->id,
        ]);
    }
}
