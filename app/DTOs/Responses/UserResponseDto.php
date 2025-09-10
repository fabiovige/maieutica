<?php

declare(strict_types=1);

namespace App\DTOs\Responses;

use App\Models\User;

class UserResponseDto extends AbstractResponseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $phone,
        public bool $isActive,
        public bool $allow,
        public ?string $avatar,
        public string $roleName,
        public ?string $createdAt,
        public ?string $updatedAt,
        public ?string $postalCode = null,
        public ?string $street = null,
        public ?string $number = null,
        public ?string $complement = null,
        public ?string $neighborhood = null,
        public ?string $city = null,
        public ?string $state = null
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            phone: $user->phone ?? '',
            isActive: $user->isActive(),
            allow: $user->allow ?? true,
            avatar: $user->avatar,
            roleName: $user->getRoleNames()->first() ?? 'user',
            createdAt: $user->created_at?->format('d/m/Y H:i'),
            updatedAt: $user->updated_at?->format('d/m/Y H:i'),
            postalCode: $user->postal_code,
            street: $user->street,
            number: $user->number,
            complement: $user->complement,
            neighborhood: $user->neighborhood,
            city: $user->city,
            state: $user->state
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->isActive,
            'allow' => $this->allow,
            'avatar' => $this->avatar,
            'role_name' => $this->roleName,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'address' => $this->getFormattedAddress(),
        ];
    }

    public function toMinimalArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role_name' => $this->roleName,
            'is_active' => $this->isActive,
        ];
    }

    private function getFormattedAddress(): ?array
    {
        if (empty($this->street) && empty($this->postalCode)) {
            return null;
        }

        return [
            'postal_code' => $this->postalCode,
            'street' => $this->street,
            'number' => $this->number,
            'complement' => $this->complement,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->state,
            'formatted' => $this->getFormattedAddressString(),
        ];
    }

    private function getFormattedAddressString(): string
    {
        $parts = array_filter([
            $this->street,
            $this->number,
            $this->complement,
            $this->neighborhood,
            $this->city,
            $this->state,
        ]);

        return implode(', ', $parts);
    }
}