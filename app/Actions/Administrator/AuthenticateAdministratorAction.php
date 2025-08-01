<?php

namespace App\Actions\Administrator;

use Domain\Administrator\Dto\AuthenticateAdministratorDto;
use Domain\Administrator\Exceptions\InvalidCredentialsException;
use Domain\Administrator\Services\AdministratorService;

/**
 * Action d'authentification d'un administrateur
 * Orchestre le processus d'authentification entre les couches
 */
class AuthenticateAdministratorAction
{
    public function __construct(
        private readonly AdministratorService $administratorService
    ) {}

    /**
     * Execute l'authentification d'un admin
     *
     * @param  array<string, string>  $credentials
     * @return array<string, mixed>
     *
     * @throws InvalidCredentialsException
     * @throws \RuntimeException
     */
    public function execute(array $credentials): array
    {
        try {
            $dto = AuthenticateAdministratorDto::fromArray($credentials);

            return $this->administratorService->authenticate($dto);

        } catch (InvalidCredentialsException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \RuntimeException('Authentication failed: '.$e->getMessage());
        }
    }
}
