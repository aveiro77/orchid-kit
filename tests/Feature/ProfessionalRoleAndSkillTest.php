<?php

namespace Tests\Feature;

use App\Models\ProfessionalRole;
use App\Models\Skill;
use App\Models\User;
use Database\Seeders\ProfessionalRoleSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class ProfessionalRoleAndSkillTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_populate_master_data_correctly(): void
    {
        $this->seed(ProfessionalRoleSeeder::class);
        $this->seed(SkillSeeder::class);

        $this->assertDatabaseHas('professional_roles', ['nama' => 'Pengusaha']);
        $this->assertDatabaseHas('professional_roles', ['nama' => 'Programmer']);
        $this->assertDatabaseHas('skills', ['nama' => 'Web Development']);
        $this->assertDatabaseHas('skills', ['nama' => 'Accounting']);
    }

    public function test_admin_can_access_and_crud_professional_roles(): void
    {
        $adminRole = Role::create([
            'slug' => 'administrator',
            'name' => 'Administrator',
            'permissions' => [
                'platform.index' => true,
                'platform.master.professional_roles' => true,
            ],
        ]);

        $admin = User::factory()->create(['status_aktif' => true]);
        $admin->roles()->attach($adminRole);

        // Access screen
        $this->actingAs($admin)
            ->get(route('platform.master.professional_roles'))
            ->assertStatus(200);

        // Create
        $this->actingAs($admin)
            ->post(route('platform.master.professional_roles', ['method' => 'save']), [
                'professionalRole' => [
                    'nama' => 'Arsitek',
                ],
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $role = ProfessionalRole::where('nama', 'Arsitek')->firstOrFail();

        // Edit
        $this->actingAs($admin)
            ->post(route('platform.master.professional_roles', ['method' => 'save']), [
                'professionalRole' => [
                    'id'   => $role->id,
                    'nama' => 'Arsitek & Desainer Interior',
                ],
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('professional_roles', [
            'id'   => $role->id,
            'nama' => 'Arsitek & Desainer Interior',
        ]);

        // Delete
        $this->actingAs($admin)
            ->post(route('platform.master.professional_roles', ['method' => 'remove']), [
                'id' => $role->id,
            ])
            ->assertStatus(302);

        $this->assertDatabaseMissing('professional_roles', [
            'id' => $role->id,
        ]);
    }

    public function test_admin_can_access_and_crud_skills(): void
    {
        $adminRole = Role::create([
            'slug' => 'administrator',
            'name' => 'Administrator',
            'permissions' => [
                'platform.index' => true,
                'platform.master.skills' => true,
            ],
        ]);

        $admin = User::factory()->create(['status_aktif' => true]);
        $admin->roles()->attach($adminRole);

        // Access screen
        $this->actingAs($admin)
            ->get(route('platform.master.skills'))
            ->assertStatus(200);

        // Create
        $this->actingAs($admin)
            ->post(route('platform.master.skills', ['method' => 'save']), [
                'skill' => [
                    'nama' => 'Cyber Security',
                ],
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $skill = Skill::where('nama', 'Cyber Security')->firstOrFail();

        // Edit
        $this->actingAs($admin)
            ->post(route('platform.master.skills', ['method' => 'save']), [
                'skill' => [
                    'id'   => $skill->id,
                    'nama' => 'Information Security',
                ],
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('skills', [
            'id'   => $skill->id,
            'nama' => 'Information Security',
        ]);

        // Delete
        $this->actingAs($admin)
            ->post(route('platform.master.skills', ['method' => 'remove']), [
                'id' => $skill->id,
            ])
            ->assertStatus(302);

        $this->assertDatabaseMissing('skills', [
            'id' => $skill->id,
        ]);
    }

    public function test_unauthorized_user_cannot_access_master_data_screens(): void
    {
        $memberRole = Role::create([
            'slug' => 'anggota',
            'name' => 'Anggota',
            'permissions' => [
                'platform.index' => true,
            ],
        ]);

        $user = User::factory()->create(['status_aktif' => true]);
        $user->roles()->attach($memberRole);

        $this->actingAs($user)
            ->get(route('platform.master.professional_roles'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->get(route('platform.master.skills'))
            ->assertStatus(403);
    }

    public function test_member_can_select_multiple_professional_roles_and_skills_in_profile(): void
    {
        $memberRole = Role::create([
            'slug' => 'anggota',
            'name' => 'Anggota',
            'permissions' => [
                'platform.index' => true,
            ],
        ]);

        $user = User::factory()->create(['status_aktif' => true]);
        $user->roles()->attach($memberRole);

        $role1 = ProfessionalRole::create(['nama' => 'Pengusaha']);
        $role2 = ProfessionalRole::create(['nama' => 'Konsultan']);

        $skill1 = Skill::create(['nama' => 'Web Development']);
        $skill2 = Skill::create(['nama' => 'Digital Marketing']);

        $this->actingAs($user)
            ->post(route('platform.profile', ['method' => 'save']), [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'professionalRoles' => [$role1->id, $role2->id],
                    'skills' => [$skill1->id, $skill2->id],
                ],
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('professional_role_user', [
            'user_id' => $user->id,
            'professional_role_id' => $role1->id,
        ]);
        $this->assertDatabaseHas('professional_role_user', [
            'user_id' => $user->id,
            'professional_role_id' => $role2->id,
        ]);

        $this->assertDatabaseHas('skill_user', [
            'user_id' => $user->id,
            'skill_id' => $skill1->id,
        ]);
        $this->assertDatabaseHas('skill_user', [
            'user_id' => $user->id,
            'skill_id' => $skill2->id,
        ]);
    }
}
