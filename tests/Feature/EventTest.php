<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $member;

    protected User $anotherMember;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Roles
        $adminRole = Role::create([
            'slug' => 'administrator',
            'name' => 'Administrator',
            'permissions' => [
                'platform.index' => true,
                'platform.events' => true,
            ],
        ]);

        $memberRole = Role::create([
            'slug' => 'anggota',
            'name' => 'Anggota',
            'permissions' => [
                'platform.index' => true,
            ],
        ]);

        // Create Admin
        $this->admin = User::factory()->create([
            'status_aktif' => true,
        ]);
        $this->admin->roles()->attach($adminRole);

        // Create Members
        $this->member = User::factory()->create([
            'status_aktif' => true,
        ]);
        $this->member->roles()->attach($memberRole);

        $this->anotherMember = User::factory()->create([
            'status_aktif' => true,
        ]);
        $this->anotherMember->roles()->attach($memberRole);
    }

    public function test_admin_can_access_event_list_screen()
    {
        $response = $this->actingAs($this->admin)->get(route('platform.events'));
        $response->assertStatus(200);
    }

    public function test_unauthorized_member_cannot_access_admin_event_screens()
    {
        $response = $this->actingAs($this->member)->get(route('platform.events'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_and_edit_event()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('platform.events.create', ['method' => 'save']), [
                'event' => [
                    'judul' => 'Kajian Bisnis Syariah',
                    'lokasi' => 'Hotel Santika Pekalongan',
                    'tanggal_mulai' => now()->addDays(2)->format('Y-m-d H:i:s'),
                    'tanggal_selesai' => now()->addDays(2)->addHours(3)->format('Y-m-d H:i:s'),
                    'kuota' => 50,
                    'status' => 'published',
                    'deskripsi' => 'Pengantar Fiqih Muamalah untuk Pengusaha',
                ],
            ]);

        $response->assertRedirect(route('platform.events'));
        $this->assertDatabaseHas('events', [
            'judul' => 'Kajian Bisnis Syariah',
            'kuota' => 50,
            'status' => 'published',
        ]);
    }

    public function test_member_can_access_my_events_screen()
    {
        $response = $this->actingAs($this->member)->get(route('platform.my_events'));
        $response->assertStatus(200);
    }

    public function test_member_can_register_to_published_event_with_available_quota()
    {
        $event = Event::create([
            'judul' => 'Gathering KPMI',
            'lokasi' => 'Pekalongan',
            'tanggal_mulai' => now()->addDays(3),
            'tanggal_selesai' => now()->addDays(3)->addHours(2),
            'kuota' => 2,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->member)
            ->post(route('platform.my_events', ['method' => 'register']), [
                'event_id' => $event->id,
            ]);

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $this->member->id,
        ]);
    }

    public function test_member_cannot_register_twice_to_the_same_event()
    {
        $event = Event::create([
            'judul' => 'Gathering KPMI Unique',
            'lokasi' => 'Pekalongan',
            'tanggal_mulai' => now()->addDays(3),
            'tanggal_selesai' => now()->addDays(3)->addHours(2),
            'kuota' => 10,
            'status' => 'published',
        ]);

        // First registration
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->member->id,
        ]);

        // Attempt second registration
        $response = $this->actingAs($this->member)
            ->post(route('platform.my_events', ['method' => 'register']), [
                'event_id' => $event->id,
            ]);

        $this->assertEquals(1, EventRegistration::where('event_id', $event->id)->where('user_id', $this->member->id)->count());
    }

    public function test_registration_rejected_when_quota_is_full()
    {
        $event = Event::create([
            'judul' => 'Workshop Exclusive',
            'lokasi' => 'Pekalongan',
            'tanggal_mulai' => now()->addDays(3),
            'tanggal_selesai' => now()->addDays(3)->addHours(2),
            'kuota' => 1,
            'status' => 'published',
        ]);

        // Register first member
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $this->member->id,
        ]);

        // Attempt registration by second member
        $response = $this->actingAs($this->anotherMember)
            ->post(route('platform.my_events', ['method' => 'register']), [
                'event_id' => $event->id,
            ]);

        $this->assertDatabaseMissing('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $this->anotherMember->id,
        ]);
    }

    public function test_public_event_directory_only_shows_published_events()
    {
        $publishedEvent = Event::create([
            'judul' => 'Public Published Event',
            'lokasi' => 'Pekalongan',
            'tanggal_mulai' => now()->addDays(1),
            'tanggal_selesai' => now()->addDays(1)->addHours(2),
            'kuota' => 100,
            'status' => 'published',
        ]);

        $draftEvent = Event::create([
            'judul' => 'Private Draft Event',
            'lokasi' => 'Pekalongan',
            'tanggal_mulai' => now()->addDays(2),
            'tanggal_selesai' => now()->addDays(2)->addHours(2),
            'kuota' => 100,
            'status' => 'draft',
        ]);

        // Index page
        $response = $this->get(route('events.index'));
        $response->assertStatus(200);
        $response->assertSee('Public Published Event');
        $response->assertDontSee('Private Draft Event');

        // Show published event detail
        $responsePublished = $this->get(route('events.show', $publishedEvent->id));
        $responsePublished->assertStatus(200);

        // Show draft event detail
        $responseDraft = $this->get(route('events.show', $draftEvent->id));
        $responseDraft->assertStatus(404);
    }
}
