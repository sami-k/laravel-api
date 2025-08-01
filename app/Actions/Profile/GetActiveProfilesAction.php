<?php

namespace App\Actions\Profile;

use Domain\Profile\Services\ProfileService;

/**
 * Action de récupération des profils actifs pour l'endpoint public
 */
class GetActiveProfilesAction
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    /**
     * Execute la récupération des profils actifs
     * Filtre le champ 'statut' pour la sécurité
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws \RuntimeException
     */
    public function execute(): array
    {
        try {
            return $this->profileService->getActiveProfilesForPublic();

        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to retrieve active profiles: '.$e->getMessage());
        }
    }
}
