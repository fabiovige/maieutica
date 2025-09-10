<?php

declare(strict_types=1);

namespace App\DTOs\Responses;

use App\Models\Responsible;

class ResponsibleResponseDto extends AbstractResponseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $phone,
        public ?string $userName,
        public ?string $userEmail,
        public bool $userIsActive,
        public array $kids,
        public array $address,
        public ?string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public static function fromModel(Responsible $responsible): self
    {
        $user = $responsible->user;
        $kids = $responsible->kids->map(function ($kid) {
            return [
                'id' => $kid->id,
                'name' => $kid->name,
                'age' => $kid->getAge(),
                'initials' => $kid->getInitialsAttribute(),
            ];
        })->toArray();

        return new self(
            id: $responsible->id,
            name: $responsible->name,
            email: $responsible->email,
            phone: $responsible->getCellAttribute(),
            userName: $user?->name,
            userEmail: $user?->email,
            userIsActive: $user?->isActive() ?? false,
            kids: $kids,
            address: [
                'postal_code' => $responsible->cep,
                'street' => $responsible->logradouro,
                'number' => $responsible->numero,
                'complement' => $responsible->complemento,
                'neighborhood' => $responsible->bairro,
                'city' => $responsible->cidade,
                'state' => $responsible->estado,
            ],
            createdAt: $responsible->created_at?->format('d/m/Y H:i'),
            updatedAt: $responsible->updated_at?->format('d/m/Y H:i')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'user' => [
                'name' => $this->userName,
                'email' => $this->userEmail,
                'is_active' => $this->userIsActive,
            ],
            'kids' => $this->kids,
            'kids_count' => count($this->kids),
            'address' => $this->getFormattedAddress(),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function toMinimalArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'kids_count' => count($this->kids),
        ];
    }

    public function toSelectArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'label' => "{$this->name} ({$this->email})",
        ];
    }

    private function getFormattedAddress(): array
    {
        $formatted = array_filter([
            $this->address['street'],
            $this->address['number'],
            $this->address['complement'],
            $this->address['neighborhood'],
            $this->address['city'],
            $this->address['state'],
        ]);

        return array_merge($this->address, [
            'formatted' => implode(', ', $formatted),
            'is_empty' => empty(array_filter($this->address)),
        ]);
    }
}