<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\MemberRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MemberRepository implements MemberRepositoryInterface
{
    public function getFilteredMembers(
        ?string $kota = null,
        ?string $nama = null,
        ?int $skillId = null,
        ?int $roleId = null,
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = User::query()
            ->where('status_aktif', true);

        if (! empty($kota)) {
            $query->where('kota', 'like', '%'.trim($kota).'%');
        }

        if (! empty($nama)) {
            $query->where('name', 'like', '%'.trim($nama).'%');
        }

        if (! empty($skillId)) {
            $query->whereHas('skills', function ($q) use ($skillId) {
                $q->where('skills.id', $skillId);
            });
        }

        if (! empty($roleId)) {
            $query->whereHas('professionalRoles', function ($q) use ($roleId) {
                $q->where('professional_roles.id', $roleId);
            });
        }

        return $query->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
