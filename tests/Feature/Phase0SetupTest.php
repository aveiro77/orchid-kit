<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class Phase0SetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_and_seeders_run_successfully(): void
    {
        $this->seed();

        $this->assertDatabaseHas('roles', ['slug' => 'administrator', 'name' => 'Administrator']);
        $this->assertDatabaseHas('roles', ['slug' => 'pengurus', 'name' => 'Pengurus']);
        $this->assertDatabaseHas('roles', ['slug' => 'anggota', 'name' => 'Anggota']);

        $admin = User::where('email', 'admin@admin.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->status_aktif);
        $this->assertTrue($admin->roles()->where('slug', 'administrator')->exists());
    }

    public function test_inactive_member_is_logged_out_by_middleware(): void
    {
        $this->seed();

        $role = Role::where('slug', 'anggota')->first();

        $user = User::factory()->create([
            'status_aktif' => false,
        ]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get(route('platform.main'));

        $response->assertRedirect(route('platform.login'));
        $this->assertGuest();
    }
}
