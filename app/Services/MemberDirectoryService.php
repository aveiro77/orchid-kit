<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\MemberRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MemberDirectoryService
{
    public function __construct(
        protected MemberRepositoryInterface $memberRepository
    ) {}

    /**
     * Retrieve paginated active members with optional filters.
     */
    public function getActiveMembers(
        ?string $kota = null,
        ?string $nama = null,
        ?int $skillId = null,
        ?int $roleId = null,
        int $perPage = 12
    ): LengthAwarePaginator {
        return $this->memberRepository->getFilteredMembers($kota, $nama, $skillId, $roleId, $perPage);
    }

    /**
     * Get active member detail by ID.
     */
    public function getMemberDetail(int $id): User
    {
        return User::query()
            ->where('status_aktif', true)
            ->where('id', $id)
            ->firstOrFail();
    }
}
