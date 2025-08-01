<?php

namespace Domain\Profile\Dto;

use Illuminate\Http\UploadedFile;

readonly class UpdateProfileDto
{
    public function __construct(
        public ?string $nom = null,
        public ?string $prenom = null,
        public ?UploadedFile $image = null,
        public ?string $statut = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nom: $data['nom'] ?? null,
            prenom: $data['prenom'] ?? null,
            image: $data['image'] ?? null,
            statut: $data['statut'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'image' => $this->image,
            'statut' => $this->statut,
        ], fn ($value) => $value !== null);
    }
}
