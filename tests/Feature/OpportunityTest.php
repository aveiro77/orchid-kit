<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class OpportunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_opportunity_model_and_relationships(): void
    {
        $user = User::factory()->create(['status_aktif' => true]);

        $opportunity = Opportunity::create([
            'user_id' => $user->id,
            'tipe' => 'need',
            'judul' => 'Mencari Supplier Kain',
            'deskripsi' => 'Dibutuhkan kain katun kualitas tinggi',
            'lokasi' => 'Pekalongan',
            'tanggal_expired' => now()->addDays(7)->toDateString(),
            'status' => 'published',
        ]);

        $this->assertInstanceOf(User::class, $opportunity->user);
        $this->assertEquals($user->id, $opportunity->user->id);
        $this->assertTrue($user->opportunities->contains($opportunity));
    }

    public function test_member_can_manage_own_opportunities(): void
    {
        $memberRole = Role::create([
            'slug' => 'anggota',
            'name' => 'Anggota',
            'permissions' => [
                'platform.index' => true,
            ],
        ]);

        $member = User::factory()->create(['status_aktif' => true]);
        $member->roles()->attach($memberRole);

        // Access My Opportunities screen
        $this->actingAs($member)
            ->get(route('platform.my_opportunities'))
            ->assertStatus(200);

        // Create opportunity
        $this->actingAs($member)
            ->post(route('platform.my_opportunities', ['method' => 'save']), [
                'opportunity' => [
                    'tipe' => 'offer',
                    'judul' => 'Jasa Konveksi Seragam',
                    'deskripsi' => 'Menerima pesanan seragam kemeja dan kaos',
                    'lokasi' => 'Pekalongan Selatan',
                    'tanggal_expired' => now()->addDays(14)->format('Y-m-d'),
                    'status' => 'published',
                ],
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('opportunities', [
            'user_id' => $member->id,
            'tipe' => 'offer',
            'judul' => 'Jasa Konveksi Seragam',
            'status' => 'published',
        ]);

        $opportunity = Opportunity::where('judul', 'Jasa Konveksi Seragam')->first();

        // Update opportunity status to closed
        $this->actingAs($member)
            ->post(route('platform.my_opportunities', ['method' => 'save']), [
                'opportunity' => [
                    'id' => $opportunity->id,
                    'tipe' => 'offer',
                    'judul' => 'Jasa Konveksi Seragam (Closed)',
                    'deskripsi' => 'Pesanan sudah penuh',
                    'lokasi' => 'Pekalongan Selatan',
                    'tanggal_expired' => now()->addDays(14)->format('Y-m-d'),
                    'status' => 'closed',
                ],
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('opportunities', [
            'id' => $opportunity->id,
            'judul' => 'Jasa Konveksi Seragam (Closed)',
            'status' => 'closed',
        ]);

        // Soft delete opportunity
        $this->actingAs($member)
            ->post(route('platform.my_opportunities', ['method' => 'remove']), [
                'id' => $opportunity->id,
            ])
            ->assertStatus(302);

        $this->assertSoftDeleted('opportunities', ['id' => $opportunity->id]);
    }

    public function test_member_cannot_modify_other_members_opportunity(): void
    {
        $memberA = User::factory()->create(['status_aktif' => true]);
        $memberB = User::factory()->create(['status_aktif' => true]);

        $opportunityA = Opportunity::create([
            'user_id' => $memberA->id,
            'tipe' => 'need',
            'judul' => 'Peluang Member A',
            'status' => 'published',
        ]);

        // Member B attempts to async load Member A's opportunity in modal
        $this->actingAs($memberB)
            ->post(route('platform.my_opportunities', [
                'method' => 'asyncGetOpportunity',
                'opportunity' => $opportunityA->id,
            ]))
            ->assertStatus(403);

        // Member B attempts to update Member A's opportunity
        $this->actingAs($memberB)
            ->post(route('platform.my_opportunities', ['method' => 'save']), [
                'opportunity' => [
                    'id' => $opportunityA->id,
                    'tipe' => 'need',
                    'judul' => 'Mencoba Bajak Peluang',
                    'status' => 'closed',
                ],
            ]);

        $this->assertDatabaseHas('opportunities', [
            'id' => $opportunityA->id,
            'judul' => 'Peluang Member A',
        ]);

        // Member B attempts to delete Member A's opportunity
        $this->actingAs($memberB)
            ->post(route('platform.my_opportunities', ['method' => 'remove']), [
                'id' => $opportunityA->id,
            ]);

        $this->assertDatabaseHas('opportunities', [
            'id' => $opportunityA->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_and_pengurus_can_access_all_opportunities_and_moderate(): void
    {
        $adminRole = Role::create([
            'slug' => 'administrator',
            'name' => 'Administrator',
            'permissions' => [
                'platform.index' => true,
                'platform.opportunities' => true,
            ],
        ]);

        $admin = User::factory()->create(['status_aktif' => true]);
        $admin->roles()->attach($adminRole);

        $member = User::factory()->create(['status_aktif' => true]);
        $opportunity = Opportunity::create([
            'user_id' => $member->id,
            'tipe' => 'collaborate',
            'judul' => 'Kolaborasi Proyek IT Syariah',
            'status' => 'draft',
        ]);

        // Admin accesses screen and sees the draft opportunity
        $this->actingAs($admin)
            ->get(route('platform.opportunities'))
            ->assertStatus(200)
            ->assertSee('Kolaborasi Proyek IT Syariah');

        // Admin moderates status to published
        $this->actingAs($admin)
            ->post(route('platform.opportunities', ['method' => 'save']), [
                'opportunity' => [
                    'id' => $opportunity->id,
                    'tipe' => 'collaborate',
                    'judul' => 'Kolaborasi Proyek IT Syariah',
                    'status' => 'published',
                ],
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('opportunities', [
            'id' => $opportunity->id,
            'status' => 'published',
        ]);

        // Admin deletes opportunity
        $this->actingAs($admin)
            ->post(route('platform.opportunities', ['method' => 'remove']), [
                'id' => $opportunity->id,
            ])
            ->assertStatus(302);

        $this->assertSoftDeleted('opportunities', ['id' => $opportunity->id]);
    }

    public function test_unauthorized_member_cannot_access_admin_opportunity_screen(): void
    {
        $memberRole = Role::create([
            'slug' => 'anggota',
            'name' => 'Anggota',
            'permissions' => [
                'platform.index' => true,
            ],
        ]);

        $member = User::factory()->create(['status_aktif' => true]);
        $member->roles()->attach($memberRole);

        $this->actingAs($member)
            ->get(route('platform.opportunities'))
            ->assertStatus(403);
    }

    public function test_scheduled_command_closes_expired_published_opportunities(): void
    {
        $user = User::factory()->create(['status_aktif' => true]);

        // Expired published opportunity (should be closed)
        $expiredPublished = Opportunity::create([
            'user_id' => $user->id,
            'tipe' => 'need',
            'judul' => 'Kebutuhan Expired',
            'tanggal_expired' => now()->subDays(2)->toDateString(),
            'status' => 'published',
        ]);

        // Active published opportunity with future expiry date (should remain published)
        $activePublished = Opportunity::create([
            'user_id' => $user->id,
            'tipe' => 'offer',
            'judul' => 'Penawaran Aktif',
            'tanggal_expired' => now()->addDays(5)->toDateString(),
            'status' => 'published',
        ]);

        // Expired draft opportunity (should remain draft)
        $expiredDraft = Opportunity::create([
            'user_id' => $user->id,
            'tipe' => 'collaborate',
            'judul' => 'Draft Expired',
            'tanggal_expired' => now()->subDays(2)->toDateString(),
            'status' => 'draft',
        ]);

        // Run command
        $this->artisan('opportunities:close-expired')
            ->expectsOutputToContain('Berhasil menutup 1 peluang yang sudah expired.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('opportunities', [
            'id' => $expiredPublished->id,
            'status' => 'closed',
        ]);

        $this->assertDatabaseHas('opportunities', [
            'id' => $activePublished->id,
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('opportunities', [
            'id' => $expiredDraft->id,
            'status' => 'draft',
        ]);
    }
}
