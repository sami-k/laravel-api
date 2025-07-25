<?php

namespace Domain\Comment\Dto;

readonly class CreateCommentDto
{
    public function __construct(
        public string $contenu,
        public int $administratorId,
        public int $profileId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contenu: $data['contenu'],
            administratorId: $data['administrator_id'],
            profileId: $data['profile_id'],
        );
    }

    public function toArray(): array
    {
        return [
            'contenu' => $this->contenu,
            'administrator_id' => $this->administratorId,
            'profile_id' => $this->profileId,
        ];
    }
}
