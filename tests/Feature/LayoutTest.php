<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** Unauthenticated users are redirected to login, never see the app layout. */
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/bills')->assertRedirect('/login');
        $this->get('/billers')->assertRedirect('/login');
    }

    /** Authenticated users see the sidebar navigation. */
    public function test_authenticated_user_sees_navigation(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertOk();
        // Sidebar nav links present
        $response->assertSee(route('dashboard'), false);
        $response->assertSee(route('bills'), false);
        $response->assertSee(route('income'), false);
        $response->assertSee(route('billers'), false);
        $response->assertSee(route('payments'), false);
        $response->assertSee(route('accounts'), false);
    }

    /** The user's name and email appear in the sidebar footer. */
    public function test_user_details_appear_in_sidebar(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
    }

    /** Each main page loads without error for an authenticated user. */
    public function test_all_main_pages_load(): void
    {
        $this->actingAs($this->user);

        $pages = [
            '/dashboard',
            '/bills',
            '/recurring-bills',
            '/income',
            '/recurring-income',
            '/billers',
            '/payments',
            '/accounts',
        ];

        foreach ($pages as $page) {
            $this->get($page)->assertOk();
        }
    }

    /** The bottom nav links are present in the layout markup. */
    public function test_bottom_nav_contains_key_routes(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        // Bottom nav targets (all 4 primary items)
        $response->assertSee('Home');
        $response->assertSee('Bills');
        $response->assertSee('Income');
        $response->assertSee('Billers');
        $response->assertSee('More');
    }

    /** The collapse toggle button is present (desktop sidebar). */
    public function test_sidebar_collapse_toggle_present(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        // The Alpine store toggle function is wired up
        $response->assertSee('billy_nav_collapsed', false);
        $response->assertSee('nav.toggle()', false);
    }

    /** Logout route is accessible and redirects correctly. */
    public function test_logout_redirects_to_login(): void
    {
        $this->actingAs($this->user);

        $this->post('/logout')->assertRedirect('/');
    }
}
