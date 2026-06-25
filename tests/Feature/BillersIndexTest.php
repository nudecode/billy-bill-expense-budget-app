<?php

namespace Tests\Feature;

use App\Models\Biller;
use App\Models\Category;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillersIndexTest extends TestCase
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

    public function test_user_only_sees_their_own_billers(): void
    {
        $other = User::factory()->create();
        Biller::factory()->create(['user_id' => $other->id, 'name' => 'OtherUserBiller']);
        $mine = Biller::factory()->create(['user_id' => $this->user->id, 'name' => 'MyBiller']);

        $response = $this->actingAs($this->user)->get('/billers');

        $response->assertSee('MyBiller');
        $response->assertDontSee('OtherUserBiller');
    }

    public function test_total_paid_shows_sum_of_payments(): void
    {
        $category = Category::factory()->create();
        $biller   = Biller::factory()->create(['user_id' => $this->user->id, 'name' => 'Netflix']);

        Payment::factory()->create([
            'user_id'      => $this->user->id,
            'biller_id'    => $biller->id,
            'category_id'  => $category->id,
            'amount'       => 22.99,
            'payment_date' => now(),
        ]);
        Payment::factory()->create([
            'user_id'      => $this->user->id,
            'biller_id'    => $biller->id,
            'category_id'  => $category->id,
            'amount'       => 22.99,
            'payment_date' => now()->subMonth(),
        ]);

        $response = $this->actingAs($this->user)->get('/billers');

        $response->assertSee('45.98');
    }

    public function test_biller_with_no_payments_shows_zero(): void
    {
        Biller::factory()->create(['user_id' => $this->user->id, 'name' => 'NewBiller']);

        $response = $this->actingAs($this->user)->get('/billers');

        $response->assertSee('0.00');
    }

    public function test_search_filters_billers(): void
    {
        Biller::factory()->create(['user_id' => $this->user->id, 'name' => 'Netflix']);
        Biller::factory()->create(['user_id' => $this->user->id, 'name' => 'Spotify']);

        $response = $this->actingAs($this->user)->get('/billers?search=Net');

        $response->assertSee('Netflix');
        $response->assertDontSee('Spotify');
    }

    public function test_billers_grouped_by_letter_when_not_searching(): void
    {
        Biller::factory()->create(['user_id' => $this->user->id, 'name' => 'Adobe']);
        Biller::factory()->create(['user_id' => $this->user->id, 'name' => 'Netflix']);

        $response = $this->actingAs($this->user)->get('/billers');

        // Letter group headers present
        $response->assertSee('biller-A', false);
        $response->assertSee('biller-N', false);
    }
}
