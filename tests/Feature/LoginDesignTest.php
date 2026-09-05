<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Pages\Auth\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginDesignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RolePermissionSeeder)->run();
    }

    public function test_login_page_renders_with_cancer_care_branding_and_animations(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertSee('Cancer Care');
        $response->assertSee('cancer-ribbon');
        $response->assertSee('ribbon-pink');
        $response->assertSee('ribbon-teal');
        $response->assertSee('ribbon-lavender');
        $response->assertSee('ribbon-gold');
        $response->assertSee('cancer-glow-orb');
        $response->assertSee('মূল ওয়েবসাইটে ফিরে যান');
    }

    public function test_user_can_authenticate_via_custom_login_page(): void
    {
        $user = User::factory()->create([
            'email' => 'doctor_test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->assignRole('super_admin');

        Livewire::test(\App\Filament\Pages\Auth\Login::class)
            ->fillForm([
                'email' => 'doctor_test@example.com',
                'password' => 'password123',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);
    }
}
