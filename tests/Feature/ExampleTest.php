<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_redirects_to_dashboard(): void
    {
        // The root route redirects to dashboard, which redirects to login for guests.
        $this->get('/')->assertRedirect('/dashboard');
    }
}
