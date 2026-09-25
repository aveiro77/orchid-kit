<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\User;
use Database\Seeders\BusinessCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class BusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_category_seeder_populates_categories(): void
    {
        $this->seed(BusinessCategorySeeder::class);

        $this->assertDatabaseHas('business_categories', ['nama' => 'Kuliner', 'slug' => 'kuliner']);
        $this->assertDatabaseHas('business_categories', ['nama' => 'Fashion Muslim', 'slug' => 'fashion-muslim']);
        $this->assertDatabaseHas('business_categories', ['nama' => 'Otomotif', 'slug' => 'otomotif']);
    }

    public function test_admin_can_crud_business_categories(): void
    {
        $adminRole = Role::create([
            'slug' => 'administrator',
            'name' => 'Administrator',
            'permissions' => [
                'platform.index' => true,
                'platform.master.business_categories' => true,
            ],
        ]);

        $admin = User::factory()->create(['status_aktif' => true]);
        $admin->roles()->attach($adminRole);

        // Access screen
        $this->actingAs($admin)
            ->get(route('platform.master.business_categories'))
            ->assertStatus(200);

        // Create category
        $this->actingAs($admin)
            ->post(route('platform.master.business_categories', ['method' => 'save']), [
                'category' => [
                    'nama' => 'Kategori Baru',
                ],
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('business_categories', [
            'nama' => 'Kategori Baru',
            'slug' => 'kategori-baru',
        ]);

        $category = BusinessCategory::where('nama', 'Kategori Baru')->first();

        // Update category
        $this->actingAs($admin)
            ->post(route('platform.master.business_categories', ['method' => 'save']), [
                'category' => [
                    'id' => $category->id,
                    'nama' => 'Kategori Edit',
                    'slug' => 'kategori-edit',
                ],
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('business_categories', [
            'id' => $category->id,
            'nama' => 'Kategori Edit',
            'slug' => 'kategori-edit',
        ]);

        // Delete category
        $this->actingAs($admin)
            ->post(route('platform.master.business_categories', ['method' => 'remove']), [
                'id' => $category->id,
            ])
            ->assertStatus(302);

        $this->assertDatabaseMissing('business_categories', ['id' => $category->id]);
    }

    public function test_member_can_manage_own_businesses_and_toggle_status_vs_soft_delete(): void
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

        $category = BusinessCategory::create(['nama' => 'Kuliner', 'slug' => 'kuliner']);

        // Access screen
        $this->actingAs($member)
            ->get(route('platform.my_businesses'))
            ->assertStatus(200);

        // Add business
        $this->actingAs($member)
            ->post(route('platform.my_businesses', ['method' => 'save']), [
                'business' => [
                    'nama_usaha' => 'Batik Pekalongan Jaya',
                    'business_category_id' => $category->id,
                    'deskripsi' => 'Usaha batik tulis asli',
                    'alamat' => 'Jl. Krapyak Pekalongan',
                    'website' => 'https://batikpekalongan.com',
                    'status' => 'aktif',
                ],
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('businesses', [
            'user_id' => $member->id,
            'nama_usaha' => 'Batik Pekalongan Jaya',
            'business_category_id' => $category->id,
            'status' => 'aktif',
        ]);

        $business = Business::where('nama_usaha', 'Batik Pekalongan Jaya')->first();

        // Toggle status to non_aktif (deactivate status, not delete)
        $this->actingAs($member)
            ->post(route('platform.my_businesses', ['method' => 'toggleStatus']), [
                'id' => $business->id,
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'non_aktif',
            'deleted_at' => null, // Not deleted!
        ]);

        // Toggle status back to aktif
        $this->actingAs($member)
            ->post(route('platform.my_businesses', ['method' => 'toggleStatus']), [
                'id' => $business->id,
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'aktif',
        ]);

        // Soft delete business
        $this->actingAs($member)
            ->post(route('platform.my_businesses', ['method' => 'remove']), [
                'id' => $business->id,
            ])
            ->assertStatus(302);

        $this->assertSoftDeleted('businesses', ['id' => $business->id]);
    }

    public function test_admin_can_access_all_businesses_and_toggle_status(): void
    {
        $adminRole = Role::create([
            'slug' => 'administrator',
            'name' => 'Administrator',
            'permissions' => [
                'platform.index' => true,
                'platform.businesses' => true,
            ],
        ]);

        $admin = User::factory()->create(['status_aktif' => true]);
        $admin->roles()->attach($adminRole);

        $member = User::factory()->create(['status_aktif' => true]);
        $category = BusinessCategory::create(['nama' => 'Kuliner', 'slug' => 'kuliner']);
        $business = Business::create([
            'user_id' => $member->id,
            'business_category_id' => $category->id,
            'nama_usaha' => 'Warung Makan Khas Pekalongan',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->get(route('platform.businesses'))
            ->assertStatus(200)
            ->assertSee('Warung Makan Khas Pekalongan');

        $this->actingAs($admin)
            ->post(route('platform.businesses', ['method' => 'toggleStatus']), [
                'id' => $business->id,
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'non_aktif',
        ]);
    }

    public function test_unauthorized_member_cannot_access_admin_business_screens(): void
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
            ->get(route('platform.master.business_categories'))
            ->assertStatus(403);

        $this->actingAs($member)
            ->get(route('platform.businesses'))
            ->assertStatus(403);
    }

    public function test_public_business_directory_shows_active_businesses_and_filters(): void
    {
        $userActive = User::factory()->create(['name' => 'Pemilik Aktif', 'status_aktif' => true]);
        $userInactive = User::factory()->create(['name' => 'Pemilik Inaktif', 'status_aktif' => false]);

        $catKuliner = BusinessCategory::create(['nama' => 'Kuliner', 'slug' => 'kuliner']);
        $catOtomotif = BusinessCategory::create(['nama' => 'Otomotif', 'slug' => 'otomotif']);

        // Active business owned by active user
        $busA = Business::create([
            'user_id' => $userActive->id,
            'business_category_id' => $catKuliner->id,
            'nama_usaha' => 'Soto Tauto Pekalongan',
            'status' => 'aktif',
        ]);

        // Active business owned by active user in Otomotif
        $busB = Business::create([
            'user_id' => $userActive->id,
            'business_category_id' => $catOtomotif->id,
            'nama_usaha' => 'Bengkel Motor Syariah',
            'status' => 'aktif',
        ]);

        // Inactive business status
        $busInactive = Business::create([
            'user_id' => $userActive->id,
            'business_category_id' => $catKuliner->id,
            'nama_usaha' => 'Kedai Tutup',
            'status' => 'non_aktif',
        ]);

        // Active business owned by inactive user
        $busInactiveOwner = Business::create([
            'user_id' => $userInactive->id,
            'business_category_id' => $catKuliner->id,
            'nama_usaha' => 'Toko Baju Inaktif',
            'status' => 'aktif',
        ]);

        // Index page lists active businesses
        $response = $this->get(route('businesses.index'));
        $response->assertStatus(200);
        $response->assertSee('Soto Tauto Pekalongan');
        $response->assertSee('Bengkel Motor Syariah');
        $response->assertDontSee('Kedai Tutup');
        $response->assertDontSee('Toko Baju Inaktif');

        // Filter by category
        $resFilterCat = $this->get(route('businesses.index', ['kategori' => $catKuliner->id]));
        $resFilterCat->assertStatus(200);
        $resFilterCat->assertSee('Soto Tauto Pekalongan');
        $resFilterCat->assertDontSee('Bengkel Motor Syariah');

        // Search by keyword
        $resSearch = $this->get(route('businesses.index', ['q' => 'Bengkel']));
        $resSearch->assertStatus(200);
        $resSearch->assertSee('Bengkel Motor Syariah');
        $resSearch->assertDontSee('Soto Tauto Pekalongan');
    }

    public function test_public_business_detail_shows_active_business_and_404_for_inactive(): void
    {
        $user = User::factory()->create(['name' => 'Pak Budi', 'status_aktif' => true]);
        $cat = BusinessCategory::create(['nama' => 'Kuliner', 'slug' => 'kuliner']);

        $activeBusiness = Business::create([
            'user_id' => $user->id,
            'business_category_id' => $cat->id,
            'nama_usaha' => 'Megono Enak',
            'deskripsi' => 'Nasi megono khas Pekalongan',
            'status' => 'aktif',
        ]);

        $inactiveBusiness = Business::create([
            'user_id' => $user->id,
            'business_category_id' => $cat->id,
            'nama_usaha' => 'Megono Tutup',
            'status' => 'non_aktif',
        ]);

        $this->get(route('businesses.show', $activeBusiness->id))
            ->assertStatus(200)
            ->assertSee('Megono Enak')
            ->assertSee('Nasi megono khas Pekalongan')
            ->assertSee('Pak Budi');

        $this->get(route('businesses.show', $inactiveBusiness->id))
            ->assertStatus(404);
    }
}
