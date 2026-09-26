<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Opportunity;
use App\Models\ProfessionalRole;
use App\Models\Referral;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class DashboardAndSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::firstOrCreate(
            ['slug' => 'administrator'],
            ['name' => 'Administrator', 'permissions' => ['platform.index' => 1, 'platform.opportunities' => 1]]
        );
        Role::firstOrCreate(
            ['slug' => 'pengurus'],
            ['name' => 'Pengurus', 'permissions' => ['platform.index' => 1, 'platform.opportunities' => 1]]
        );
        Role::firstOrCreate(
            ['slug' => 'anggota'],
            ['name' => 'Anggota', 'permissions' => ['platform.index' => 1]]
        );
    }

    public function test_admin_dashboard_displays_correct_metrics_and_widgets(): void
    {
        $admin = User::factory()->create([
            'status_aktif' => true,
        ]);
        $adminRole = Role::where('slug', 'administrator')->first();
        $admin->roles()->attach($adminRole);

        $member = User::factory()->create(['status_aktif' => true]);
        $category = BusinessCategory::create(['nama' => 'Kuliner', 'slug' => 'kuliner']);

        Business::create([
            'user_id'              => $member->id,
            'business_category_id' => $category->id,
            'nama_usaha'           => 'Kedai Kopi Pekalongan',
            'status'               => 'aktif',
        ]);

        Opportunity::create([
            'user_id' => $member->id,
            'tipe'    => 'offer',
            'judul'   => 'Penawaran Kerjasama Batik',
            'status'  => 'published',
        ]);

        Referral::create([
            'pemberi_referral_id'  => $admin->id,
            'penerima_referral_id' => $member->id,
            'client_name'          => 'Client A',
            'project_name'         => 'Project X',
            'nilai_estimasi'       => 1000000,
            'status'               => 'introduced',
        ]);

        Event::create([
            'judul'           => 'Kopdar Pekalongan',
            'tanggal_mulai'   => now()->addDays(2),
            'tanggal_selesai' => now()->addDays(2)->addHours(2),
            'status'          => 'published',
        ]);

        $response = $this->actingAs($admin)->get(route('platform.main'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Administrator');
        $response->assertSee('Total Anggota');
        $response->assertSee('Penawaran Kerjasama Batik');
        $response->assertSee('Kopdar Pekalongan');
    }

    public function test_member_dashboard_displays_personal_summary_widgets(): void
    {
        $member = User::factory()->create([
            'name'         => 'Budi Anggota',
            'status_aktif' => true,
            'kota'         => 'Pekalongan',
        ]);
        $anggotaRole = Role::where('slug', 'anggota')->first();
        $member->roles()->attach($anggotaRole);

        $skill = Skill::create(['nama' => 'Web Development']);
        $member->skills()->attach($skill);

        $category = BusinessCategory::create(['nama' => 'Teknologi', 'slug' => 'teknologi']);
        Business::create([
            'user_id'              => $member->id,
            'business_category_id' => $category->id,
            'nama_usaha'           => 'Budi Software House',
            'status'               => 'aktif',
        ]);

        Opportunity::create([
            'user_id' => $member->id,
            'tipe'    => 'need',
            'judul'   => 'Butuh UI Designer',
            'status'  => 'published',
        ]);

        $event = Event::create([
            'judul'           => 'Seminar Bisnis Syariah',
            'tanggal_mulai'   => now()->addDays(5),
            'tanggal_selesai' => now()->addDays(5)->addHours(3),
            'status'          => 'published',
        ]);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id'  => $member->id,
        ]);

        $response = $this->actingAs($member)->get(route('platform.main'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Anggota');
        $response->assertSee('Budi Anggota');
        $response->assertSee('Web Development');
        $response->assertSee('Budi Software House');
        $response->assertSee('Butuh UI Designer');
        $response->assertSee('Seminar Bisnis Syariah');
    }

    public function test_public_member_directory_filters_by_kota_nama_skill_and_peran(): void
    {
        $skillDev = Skill::create(['nama' => 'Laravel']);
        $skillDes = Skill::create(['nama' => 'Graphic Design']);

        $roleManager = ProfessionalRole::create(['nama' => 'Manager']);
        $roleDev = ProfessionalRole::create(['nama' => 'Developer']);

        $member1 = User::factory()->create([
            'name'         => 'Ahmad Pekalongan',
            'kota'         => 'Pekalongan',
            'status_aktif' => true,
        ]);
        $member1->skills()->attach($skillDev);
        $member1->professionalRoles()->attach($roleDev);

        $member2 = User::factory()->create([
            'name'         => 'Budi Semarang',
            'kota'         => 'Semarang',
            'status_aktif' => true,
        ]);
        $member2->skills()->attach($skillDes);
        $member2->professionalRoles()->attach($roleManager);

        // Filter by combined kota and skill
        $response = $this->get(route('members.index', [
            'kota'  => 'Pekalongan',
            'skill' => $skillDev->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Ahmad Pekalongan');
        $response->assertDontSee('Budi Semarang');

        // Filter by combined peran profesi
        $response2 = $this->get(route('members.index', [
            'peran' => $roleManager->id,
        ]));

        $response2->assertStatus(200);
        $response2->assertSee('Budi Semarang');
        $response2->assertDontSee('Ahmad Pekalongan');
    }

    public function test_public_business_directory_filters_by_category_and_status(): void
    {
        $cat1 = BusinessCategory::create(['nama' => 'Kuliner', 'slug' => 'kuliner']);
        $cat2 = BusinessCategory::create(['nama' => 'Fashion', 'slug' => 'fashion']);

        $user = User::factory()->create(['status_aktif' => true]);

        Business::create([
            'user_id'              => $user->id,
            'business_category_id' => $cat1->id,
            'nama_usaha'           => 'Batik Kuliner Pekalongan',
            'status'               => 'aktif',
        ]);

        Business::create([
            'user_id'              => $user->id,
            'business_category_id' => $cat2->id,
            'nama_usaha'           => 'Batik Fashion Pekalongan',
            'status'               => 'non_aktif',
        ]);

        // Filter active & Kuliner
        $response = $this->get(route('businesses.index', [
            'kategori' => $cat1->id,
            'status'   => 'aktif',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Batik Kuliner Pekalongan');
        $response->assertDontSee('Batik Fashion Pekalongan');

        // Filter non_aktif
        $response2 = $this->get(route('businesses.index', [
            'status' => 'non_aktif',
        ]));

        $response2->assertStatus(200);
        $response2->assertSee('Batik Fashion Pekalongan');
    }

    public function test_opportunity_screen_filtering_by_tipe_and_status(): void
    {
        $admin = User::factory()->create(['status_aktif' => true]);
        $adminRole = Role::where('slug', 'administrator')->first();
        $admin->roles()->attach($adminRole);

        Opportunity::create([
            'user_id' => $admin->id,
            'tipe'    => 'need',
            'judul'   => 'Kebutuhan Bahan Baku',
            'status'  => 'published',
        ]);

        Opportunity::create([
            'user_id' => $admin->id,
            'tipe'    => 'offer',
            'judul'   => 'Penawaran Produk Terbaru',
            'status'  => 'draft',
        ]);

        $response = $this->actingAs($admin)->get(route('platform.opportunities', [
            'filter' => [
                'tipe'   => 'need',
                'status' => 'published',
            ],
        ]));

        $response->assertStatus(200);
        $response->assertSee('Kebutuhan Bahan Baku');
        $response->assertDontSee('Penawaran Produk Terbaru');
    }
}
