<?php

namespace Tests\Feature\Foundation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_the_admin_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_admin_login_page_renders_for_a_guest(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_an_admin_user_can_reach_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_a_non_admin_user_is_forbidden_from_the_admin_area(): void
    {
        $this->actingAs(User::factory()->nonAdmin()->create())
            ->get('/admin/dashboard')
            ->assertForbidden();
    }
}
