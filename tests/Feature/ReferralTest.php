<?php

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $memberA;
    protected User $memberB;
    protected User $memberC;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'slug'        => 'administrator',
            'name'        => 'Administrator',
            'permissions' => [
                'platform.index'     => true,
                'platform.referrals' => true,
            ],
        ]);

        $memberRole = Role::create([
            'slug'        => 'anggota',
            'name'        => 'Anggota',
            'permissions' => [
                'platform.index' => true,
            ],
        ]);

        $this->admin = User::factory()->create([
            'status_aktif' => true,
        ]);
        $this->admin->addRole($adminRole);

        $this->memberA = User::factory()->create([
            'name'         => 'Member A',
            'status_aktif' => true,
        ]);
        $this->memberA->addRole($memberRole);

        $this->memberB = User::factory()->create([
            'name'         => 'Member B',
            'status_aktif' => true,
        ]);
        $this->memberB->addRole($memberRole);

        $this->memberC = User::factory()->create([
            'name'         => 'Member C',
            'status_aktif' => true,
        ]);
        $this->memberC->addRole($memberRole);
    }

    public function test_referral_model_relationships(): void
    {
        $referral = Referral::create([
            'pemberi_referral_id'  => $this->memberA->id,
            'penerima_referral_id' => $this->memberB->id,
            'client_name'          => 'PT Klien A',
            'project_name'         => 'Proyek A',
            'nilai_estimasi'       => 15000000,
            'status'               => 'introduced',
            'catatan'              => 'Perkenalan pertama',
        ]);

        $this->assertEquals('Member A', $referral->pemberi->name);
        $this->assertEquals('Member B', $referral->penerima->name);

        $this->assertCount(1, $this->memberA->referralsGiven);
        $this->assertCount(1, $this->memberB->referralsReceived);
    }

    public function test_member_can_access_my_referrals_screen_and_see_only_involved_referrals(): void
    {
        // Referral 1: A gives to B
        $ref1 = Referral::create([
            'pemberi_referral_id'  => $this->memberA->id,
            'penerima_referral_id' => $this->memberB->id,
            'client_name'          => 'Klien AB',
            'project_name'         => 'Proyek AB',
            'status'               => 'introduced',
        ]);

        // Referral 2: C gives to B
        $ref2 = Referral::create([
            'pemberi_referral_id'  => $this->memberC->id,
            'penerima_referral_id' => $this->memberB->id,
            'client_name'          => 'Klien CB',
            'project_name'         => 'Proyek CB',
            'status'               => 'follow_up',
        ]);

        // Referral 3: C gives to Admin
        $ref3 = Referral::create([
            'pemberi_referral_id'  => $this->memberC->id,
            'penerima_referral_id' => $this->admin->id,
            'client_name'          => 'Klien CA',
            'project_name'         => 'Proyek CA',
            'status'               => 'negotiation',
        ]);

        // Member A accesses my-referrals screen
        $responseA = $this->actingAs($this->memberA)->get(route('platform.my_referrals'));
        $responseA->assertOk();
        $responseA->assertSee('Klien AB');
        $responseA->assertDontSee('Klien CB');
        $responseA->assertDontSee('Klien CA');

        // Member B accesses my-referrals screen (involved in ref1 as penerima and ref2 as penerima)
        $responseB = $this->actingAs($this->memberB)->get(route('platform.my_referrals'));
        $responseB->assertOk();
        $responseB->assertSee('Klien AB');
        $responseB->assertSee('Klien CB');
        $responseB->assertDontSee('Klien CA');
    }

    public function test_member_can_create_new_referral(): void
    {
        $response = $this->actingAs($this->memberA)->post(route('platform.my_referrals', [
            'method' => 'save',
        ]), [
            'referral' => [
                'penerima_referral_id' => $this->memberB->id,
                'client_name'          => 'PT Klien Baru',
                'project_name'         => 'Website Modern',
                'nilai_estimasi'       => 25000000,
                'status'               => 'introduced',
                'catatan'              => 'Member B berpengalaman di bidang ini',
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('referrals', [
            'pemberi_referral_id'  => $this->memberA->id,
            'penerima_referral_id' => $this->memberB->id,
            'client_name'          => 'PT Klien Baru',
            'project_name'         => 'Website Modern',
            'nilai_estimasi'       => 25000000,
            'status'               => 'introduced',
        ]);
    }

    public function test_recipient_member_can_update_status_and_notes(): void
    {
        $referral = Referral::create([
            'pemberi_referral_id'  => $this->memberA->id,
            'penerima_referral_id' => $this->memberB->id,
            'client_name'          => 'Klien X',
            'project_name'         => 'Proyek X',
            'status'               => 'introduced',
        ]);

        // Member B (penerima) updates status to 'won' and adds notes
        $response = $this->actingAs($this->memberB)->post(route('platform.my_referrals', [
            'method' => 'save',
        ]), [
            'referral' => [
                'id'             => $referral->id,
                'client_name'    => 'Klien X',
                'project_name'   => 'Proyek X',
                'nilai_estimasi' => 10000000,
                'status'         => 'won',
                'catatan'        => 'Deal ditandatangani hari ini!',
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('referrals', [
            'id'             => $referral->id,
            'status'         => 'won',
            'catatan'        => 'Deal ditandatangani hari ini!',
            'nilai_estimasi' => 10000000,
        ]);
    }

    public function test_member_cannot_modify_or_view_unauthorized_referral(): void
    {
        // Referral between A and B
        $referral = Referral::create([
            'pemberi_referral_id'  => $this->memberA->id,
            'penerima_referral_id' => $this->memberB->id,
            'client_name'          => 'Klien Privasi',
            'project_name'         => 'Proyek Privasi',
            'status'               => 'introduced',
        ]);

        // Member C attempts to edit/save referral
        $responseSave = $this->actingAs($this->memberC)->post(route('platform.my_referrals', [
            'method' => 'save',
        ]), [
            'referral' => [
                'id'           => $referral->id,
                'client_name'  => 'Hack Name',
                'project_name' => 'Hack Proyek',
                'status'       => 'won',
            ],
        ]);

        $responseSave->assertStatus(404);

        // Member C attempts to delete referral
        $responseRemove = $this->actingAs($this->memberC)->post(route('platform.my_referrals', [
            'method' => 'remove',
        ]), [
            'id' => $referral->id,
        ]);

        $responseRemove->assertStatus(404);
    }

    public function test_admin_can_view_and_manage_all_referrals(): void
    {
        $referral = Referral::create([
            'pemberi_referral_id'  => $this->memberA->id,
            'penerima_referral_id' => $this->memberB->id,
            'client_name'          => 'Klien Admin Test',
            'project_name'         => 'Proyek Admin Test',
            'status'               => 'introduced',
        ]);

        // Admin accesses referral list screen
        $response = $this->actingAs($this->admin)->get(route('platform.referrals'));
        $response->assertOk();
        $response->assertSee('Klien Admin Test');

        // Admin updates referral
        $responseSave = $this->actingAs($this->admin)->post(route('platform.referrals', [
            'method' => 'save',
        ]), [
            'referral' => [
                'id'                   => $referral->id,
                'pemberi_referral_id'  => $this->memberA->id,
                'penerima_referral_id' => $this->memberB->id,
                'client_name'          => 'Klien Admin Test Updated',
                'project_name'         => 'Proyek Admin Test Updated',
                'status'               => 'follow_up',
            ],
        ]);

        $responseSave->assertSessionHasNoErrors();
        $this->assertDatabaseHas('referrals', [
            'id'          => $referral->id,
            'client_name' => 'Klien Admin Test Updated',
            'status'      => 'follow_up',
        ]);
    }

    public function test_unauthorized_member_cannot_access_admin_referrals_screen(): void
    {
        $response = $this->actingAs($this->memberA)->get(route('platform.referrals'));
        $response->assertStatus(403);
    }

    public function test_soft_delete_referral(): void
    {
        $referral = Referral::create([
            'pemberi_referral_id'  => $this->memberA->id,
            'penerima_referral_id' => $this->memberB->id,
            'client_name'          => 'Klien Soft Delete',
            'project_name'         => 'Proyek Soft Delete',
            'status'               => 'introduced',
        ]);

        $response = $this->actingAs($this->memberA)->post(route('platform.my_referrals', [
            'method' => 'remove',
        ]), [
            'id' => $referral->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSoftDeleted('referrals', [
            'id' => $referral->id,
        ]);
    }
}
