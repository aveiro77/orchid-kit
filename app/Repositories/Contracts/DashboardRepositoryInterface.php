<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    /**
     * Get aggregate metrics and latest data for Admin / Pengurus dashboard.
     */
    public function getAdminMetrics(): array;

    /**
     * Get summary metrics and personal data for member dashboard.
     */
    public function getMemberMetrics(int $userId): array;
}
