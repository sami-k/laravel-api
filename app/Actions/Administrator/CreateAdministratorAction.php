<?php

namespace App\Actions\Administrator;

use Domain\Administrator\Dto\CreateAdministratorDto;
use Domain\Administrator\Exceptions\AdministratorAlreadyExistsException;
use Domain\Administrator\Services\AdministratorService;

/**
 * Action de création d'un administrateur
 */
class CreateAdministratorAction
{
    public function __construct(
        private readonly AdministratorService $administratorService
    ) {}

    /**
     * Execute la création d'un administrateur
     *
     * @param  array<string, string>  $data
     *
     * @throws AdministratorAlreadyExistsException
     * @throws \RuntimeException
     */
    public function execute(array $data): int
    {
        try {
            $dto = CreateAdministratorDto::fromArray($data);

            return $this->administratorService->create($dto);

        } catch (AdministratorAlreadyExistsException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \RuntimeException('Administrator creation failed: '.$e->getMessage());
        }
    }
}
