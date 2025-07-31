<?php

namespace App\Actions\Profile;

use Domain\Profile\Dto\UpdateProfileDto;
use Domain\Profile\Services\ProfileService;
use Domain\Profile\Repositories\ProfileRepositoryInterface;
use Domain\Profile\Exceptions\ProfileNotFoundException;
use Domain\Profile\Exceptions\InvalidImageException;

/**
 * Action de mise à jour d'un profil
 */
class UpdateProfileAction
{
    public function __construct(
        private readonly ProfileService $profileService,
        private readonly ProfileRepositoryInterface $profileRepository
    ) {}

    /**
     * Execute la mise à jour d'un profil
     *
     * @param array<string, mixed> $data
     * @throws ProfileNotFoundException
     * @throws InvalidImageException
     * @throws \RuntimeException
     */
    public function execute(int $profileId, array $data): bool
    {
        try {
            $profile = $this->profileRepository->findById($profileId);

            if ($profile === null) {
                throw new ProfileNotFoundException("Profile with ID {$profileId} not found");
            }

            $dto = UpdateProfileDto::fromArray($data);

            return $this->profileService->update($profile, $dto);

        } catch (ProfileNotFoundException|InvalidImageException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \RuntimeException('Profile update failed: ' . $e->getMessage());
        }
    }
}
