<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Biller;
use App\Models\Category;
use App\Models\Frequency;
use App\Models\RecurringBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillsCalendarTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_bills_page_loads(): void
    {
        $this->actingAs($this->user)->get('/bills')->assertOk();
    }

    public function test_defaults_to_week_view(): void
    {
        $response = $this->actingAs($this->user)->get('/bills');
        // Week view wires the previous arrow to previousWeek (not previousMonth)
        $response->assertSee('previousWeek', false);
        // Toggle buttons present
        $response->assertSee('Week');
        $response->assertSee('Month');
    }

    public function test_month_view_renders_calendar_grid(): void
    {
        $response = $this->actingAs($this->user)->get('/bills?calView=month&selectedDate=');
        $response->assertOk();
        // Day headers
        $response->assertSee('Mon');
        $response->assertSee('Sun');
    }

    public function test_week_view_shows_day_headers(): void
    {
        $response = $this->actingAs($this->user)->get('/bills?calView=week');
        $response->assertOk();
        $response->assertSee('Mon');
        $response->assertSee('Tue');
        $response->assertSee('Sat');
        $response->assertSee('Sun');
    }

    public function test_selecting_a_date_filters_bill_list(): void
    {
        $this->seedBill('2026-06-10', 50.00);
        $this->seedBill('2026-06-15', 99.00);

        $response = $this->actingAs($this->user)->get('/bills?y=2026&m=6&selectedDate=2026-06-10');

        $response->assertOk();
        $response->assertSee('50.00');
        $response->assertDontSee('99.00');
    }

    public function test_show_all_link_present_when_date_selected(): void
    {
        $response = $this->actingAs($this->user)->get('/bills?selectedDate=2026-06-10');
        $response->assertSee('Show all');
    }

    public function test_tabs_visible_when_no_date_selected(): void
    {
        $response = $this->actingAs($this->user)->get('/bills?selectedDate=');
        $response->assertSee('Unpaid');
        $response->assertSee('Paid');
    }

    public function test_user_only_sees_their_own_bills(): void
    {
        $other = User::factory()->create();
        $this->seedBillForUser($other, '2026-06-10', 500.00);

        $response = $this->actingAs($this->user)->get('/bills?y=2026&m=6&selectedDate=2026-06-10');

        $response->assertDontSee('500.00');
    }

    public function test_summary_cards_always_show_month_totals(): void
    {
        $response = $this->actingAs($this->user)->get('/bills?y=2026&m=6&selectedDate=2026-06-10');
        // Cards render regardless of selected date
        $response->assertSee('Paid');
        $response->assertSee('Unpaid');
        $response->assertSee('Total');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function seedBill(string $startDate, float $amount): void
    {
        $this->seedBillForUser($this->user, $startDate, $amount);
    }

    private function seedBillForUser(User $user, string $startDate, float $amount): void
    {
        RecurringBill::factory()->create([
            'user_id'      => $user->id,
            'biller_id'    => Biller::factory()->create(['user_id' => $user->id])->id,
            'frequency_id' => Frequency::factory()->create(['name' => 'Monthly', 'date_add_unit' => 'month', 'date_add_value' => 1])->id,
            'category_id'  => Category::factory()->create()->id,
            'account_id'   => Account::factory()->create(['user_id' => $user->id])->id,
            'amount'       => $amount,
            'start_date'   => $startDate,
            'end_date'     => Carbon::parse($startDate)->addYear()->toDateString(),
        ]);
    }
}
