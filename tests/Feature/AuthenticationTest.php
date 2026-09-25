<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_and_are_assigned_anggota_role(): void
    {
        $this->seed();

        $response = $this->post('/register', [
            'name'                  => 'Test Member',
            'email'                 => 'member@example.com',
            'nomor_wa'              => '081234567890',
            'kota'                  => 'Pekalongan',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('platform.index'));

        $user = User::where('email', 'member@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->status_aktif);
        $this->assertEquals('081234567890', $user->nomor_wa);
        $this->assertEquals('Pekalongan', $user->kota);

        $this->assertTrue($user->roles()->where('slug', 'anggota')->exists());
    }

    public function test_inactive_user_is_logged_out_when_accessing_panel(): void
    {
        $this->seed();

        $role = Role::where('slug', 'anggota')->first();

        $user = User::factory()->create([
            'status_aktif' => false,
        ]);
        if ($role) {
            $user->roles()->attach($role);
        }

        $response = $this->actingAs($user)->get(route('platform.index'));

        $response->assertRedirect(route('platform.login'));
        $this->assertGuest();
    }
}
