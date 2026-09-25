<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MemberDirectoryService
{
    /**
     * Retrieve paginated active members with optional filters.
     *
     * @param string|null $kota
     * @param string|null $nama
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActiveMembers(?string $kota = null, ?string $nama = null, int $perPage = 12): LengthAwarePaginator
    {
        $query = User::query()
            ->where('status_aktif', true);

        if (!empty($kota)) {
            $query->where('kota', 'like', '%' . trim($kota) . '%');
        }

        if (!empty($nama)) {
            $query->where('name', 'like', '%' . trim($nama) . '%');
        }

        return $query->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get active member detail by ID.
     *
     * @param int $id
     * @return User
     */
    public function getMemberDetail(int $id): User
    {
        return User::query()
            ->where('status_aktif', true)
            ->where('id', $id)
            ->firstOrFail();
    }
}
