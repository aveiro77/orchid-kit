<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class MemberDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_member_can_access_and_update_profile_screen_in_orchid(): void
    {
        $role = Role::create([
            'slug' => 'anggota',
            'name' => 'Anggota',
            'permissions' => [
                'platform.index' => true,
            ],
        ]);

        $user = User::factory()->create([
            'name' => 'Original Name',
            'kota' => 'Batang',
            'status_aktif' => true,
        ]);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('platform.profile'))
            ->assertStatus(200);

        $this->actingAs($user)
            ->post(route('platform.profile', ['method' => 'save']), [
                'user' => [
                    'name' => 'Updated Name',
                    'email' => $user->email,
                    'nomor_wa' => '08123456789',
                    'kota' => 'Pekalongan',
                    'bio' => 'Pengusaha sukses',
                    'linkedin' => 'https://linkedin.com/in/updated',
                    'website' => 'https://updated.com',
                    'instagram' => '@updated_ig',
                    'foto' => '/storage/test.jpg',
                ],
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'nomor_wa' => '08123456789',
            'kota' => 'Pekalongan',
            'bio' => 'Pengusaha sukses',
            'linkedin' => 'https://linkedin.com/in/updated',
            'website' => 'https://updated.com',
            'instagram' => '@updated_ig',
            'foto' => '/storage/test.jpg',
        ]);
    }

    public function test_public_member_directory_lists_only_active_members(): void
    {
        $activeUser = User::factory()->create([
            'name' => 'Active Member',
            'status_aktif' => true,
        ]);

        $inactiveUser = User::factory()->create([
            'name' => 'Inactive Member',
            'status_aktif' => false,
        ]);

        $response = $this->get(route('members.index'));

        $response->assertStatus(200);
        $response->assertSee('Active Member');
        $response->assertDontSee('Inactive Member');
    }

    public function test_public_member_directory_filters_by_kota_and_nama(): void
    {
        $userA = User::factory()->create([
            'name' => 'Ahmad Budiman',
            'kota' => 'Pekalongan',
            'status_aktif' => true,
        ]);

        $userB = User::factory()->create([
            'name' => 'Budi Santoso',
            'kota' => 'Semarang',
            'status_aktif' => true,
        ]);

        // Filter by nama
        $responseNama = $this->get(route('members.index', ['nama' => 'Ahmad']));
        $responseNama->assertStatus(200);
        $responseNama->assertSee('Ahmad Budiman');
        $responseNama->assertDontSee('Budi Santoso');

        // Filter by kota
        $responseKota = $this->get(route('members.index', ['kota' => 'Semarang']));
        $responseKota->assertStatus(200);
        $responseKota->assertSee('Budi Santoso');
        $responseKota->assertDontSee('Ahmad Budiman');
    }

    public function test_public_member_detail_page_shows_active_member_and_returns_404_for_inactive_member(): void
    {
        $activeUser = User::factory()->create([
            'name' => 'Active Detail Member',
            'bio' => 'Bio detail active',
            'status_aktif' => true,
        ]);

        $inactiveUser = User::factory()->create([
            'name' => 'Inactive Detail Member',
            'status_aktif' => false,
        ]);

        $this->get(route('members.show', $activeUser->id))
            ->assertStatus(200)
            ->assertSee('Active Detail Member')
            ->assertSee('Bio detail active');

        $this->get(route('members.show', $inactiveUser->id))
            ->assertStatus(404);
    }
}
