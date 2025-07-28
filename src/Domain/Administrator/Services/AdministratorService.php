<?php

namespace Domain\Administrator\Services;

use Domain\Administrator\Dto\CreateAdministratorDto;
use Domain\Administrator\Dto\AuthenticateAdministratorDto;
use Domain\Administrator\Repositories\AdministratorRepositoryInterface;
use Domain\Administrator\Exceptions\InvalidCredentialsException;
use Domain\Administrator\Exceptions\AdministratorAlreadyExistsException;
use Illuminate\Support\Facades\Hash;

class AdministratorService
{
    public function __construct(
        private readonly AdministratorRepositoryInterface $repository
    ) {}

    /**
     * Authentifie un administrateur et retourne les données avec token
     */
    public function authenticate(AuthenticateAdministratorDto $dto): array
    {
        $administrator = $this->repository->findByEmail($dto->email);

        if (!$administrator || !Hash::check($dto->password, $administrator->password)) {
            throw new InvalidCredentialsException('Invalid email or password');
        }

        // Génération du token Sanctum
        $token = $administrator->createToken('api-token')->plainTextToken;

        return [
            'administrator' => [
                'id' => $administrator->id,
                'name' => $administrator->name,
                'email' => $administrator->email,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Crée un nouvel administrateur
     */
    public function create(CreateAdministratorDto $dto): int
    {
        // Vérification de l'unicité de l'email
        if ($this->repository->existsByEmail($dto->email)) {
            throw new AdministratorAlreadyExistsException("Administrator with email {$dto->email} already exists");
        }

        // Hachage du mot de passe
        $createDto = new CreateAdministratorDto(
            name: $dto->name,
            email: $dto->email,
            password: Hash::make($dto->password)
        );

        return $this->repository->create($createDto);
    }

    /**
     * Vérifie si un administrateur existe par email
     */
    public function existsByEmail(string $email): bool
    {
        return $this->repository->existsByEmail($email);
    }

    /**
     * Révoque tous les tokens d'un administrateur
     */
    public function logout(object $administrator): bool
    {
        $administrator->tokens()->delete();
        return true;
    }
}
