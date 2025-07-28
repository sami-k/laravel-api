<?php

namespace App\Actions\Profile;

use Domain\Profile\Services\ProfileService;

/**
 * Action pour récupérer les profils actifs (endpoint public)
 */
class GetActiveProfilesAction
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    /**
     * Execute la récupération des profils actifs
     */
    public function execute(): array
    {
        try {
            return $this->profileService->getActiveProfilesForPublic();

        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to retrieve active profiles: ' . $e->getMessage());
        }
    }
}
