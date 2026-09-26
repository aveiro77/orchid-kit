<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Opportunity;
use App\Models\ProfessionalRole;
use App\Models\Referral;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Orchid\Platform\Models\Role;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $anggotaRole = Role::where('slug', 'anggota')->first();
        $pengurusRole = Role::where('slug', 'pengurus')->first();

        $roles = ProfessionalRole::all();
        $skills = Skill::all();
        $categories = BusinessCategory::all();

        // 1. Create Pengurus Users
        $pengurusList = User::factory()->count(3)->create([
            'status_aktif' => true,
        ]);
        foreach ($pengurusList as $p) {
            if ($pengurusRole) {
                $p->roles()->syncWithoutDetaching([$pengurusRole->id]);
            }
            if ($roles->isNotEmpty()) {
                $p->professionalRoles()->sync($roles->random(rand(1, 2))->pluck('id'));
            }
            if ($skills->isNotEmpty()) {
                $p->skills()->sync($skills->random(rand(2, 4))->pluck('id'));
            }
        }

        // 2. Create Anggota Users
        $members = User::factory()->count(15)->create([
            'status_aktif' => true,
        ]);
        foreach ($members as $m) {
            if ($anggotaRole) {
                $m->roles()->syncWithoutDetaching([$anggotaRole->id]);
            }
            if ($roles->isNotEmpty()) {
                $m->professionalRoles()->sync($roles->random(rand(1, 2))->pluck('id'));
            }
            if ($skills->isNotEmpty()) {
                $m->skills()->sync($skills->random(rand(2, 5))->pluck('id'));
            }
        }

        $allUsers = $pengurusList->concat($members);

        // 3. Create Businesses
        if ($categories->isNotEmpty()) {
            foreach ($allUsers->random(12) as $u) {
                Business::factory()->create([
                    'user_id' => $u->id,
                    'business_category_id' => $categories->random()->id,
                    'status' => 'aktif',
                ]);
            }
        }

        // 4. Create Opportunities
        foreach ($allUsers->random(10) as $u) {
            Opportunity::factory()->create([
                'user_id' => $u->id,
                'status' => 'published',
            ]);
        }

        // 5. Create Referrals
        for ($i = 0; $i < 8; $i++) {
            $giver = $allUsers->random();
            $receiver = $allUsers->where('id', '!=', $giver->id)->random();

            Referral::factory()->create([
                'pemberi_referral_id' => $giver->id,
                'penerima_referral_id' => $receiver->id,
            ]);
        }

        // 6. Create Events & Registrations
        $events = Event::factory()->count(5)->create([
            'status' => 'published',
        ]);

        foreach ($events as $e) {
            $attendees = $allUsers->random(rand(3, 8));
            foreach ($attendees as $att) {
                EventRegistration::firstOrCreate([
                    'event_id' => $e->id,
                    'user_id' => $att->id,
                ], [
                    'checkin_at' => rand(0, 1) ? now() : null,
                ]);
            }
        }
    }
}
