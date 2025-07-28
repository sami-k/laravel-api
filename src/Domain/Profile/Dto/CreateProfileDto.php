<?php

namespace Domain\Profile\Dto;

use Illuminate\Http\UploadedFile;

readonly class CreateProfileDto
{
    public function __construct(
        public string $nom,
        public string $prenom,
        public UploadedFile|string|null $image,
        public string $statut,
        public int $administratorId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nom: $data['nom'],
            prenom: $data['prenom'],
            image: $data['image'] ?? null,
            statut: $data['statut'] ?? 'en_attente',
            administratorId: $data['administrator_id'],
        );
    }

    public function toArray(): array
    {
        return [
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'image' => $this->image,
            'statut' => $this->statut,
            'administrator_id' => $this->administratorId,
        ];
    }
}
