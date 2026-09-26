<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MemberRepositoryInterface
{
    /**
     * Retrieve active members with filters for city, name, skill, and professional role.
     */
    public function getFilteredMembers(
        ?string $kota = null,
        ?string $nama = null,
        ?int $skillId = null,
        ?int $roleId = null,
        int $perPage = 12
    ): LengthAwarePaginator;
}
