<?php

namespace App\Actions\Profile;

use Domain\Profile\Dto\CreateProfileDto;
use Domain\Profile\Exceptions\InvalidImageException;
use Domain\Profile\Services\ProfileService;

/**
 * Action de création d'un profil
 */
class CreateProfileAction
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    /**
     * Execute la création d'un profil
     *
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidImageException
     * @throws \RuntimeException
     */
    public function execute(array $data, int $administratorId): int
    {
        try {
            // Ajout de l'ID de l'administrateur aux données
            $data['administrator_id'] = $administratorId;

            $dto = CreateProfileDto::fromArray($data);

            return $this->profileService->create($dto);

        } catch (InvalidImageException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \RuntimeException('Profile creation failed: '.$e->getMessage());
        }
    }
}
