<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Specifications\UniqueResponsibleEmailSpecification;
use App\ValueObjects\Address\AddressData;

class ResponsibleData extends AbstractValueObject
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly AddressData $address,
        public readonly ?int $userId = null,
        private readonly ?int $currentResponsibleId = null
    ) {
        $this->validate();
    }

    public static function fromArray(array $data, ?int $currentResponsibleId = null): self
    {
        return new self(
            name: trim($data['name']),
            email: trim(strtolower($data['email'])),
            phone: $data['cell'] ?? $data['phone'],
            address: AddressData::fromArray($data),
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            currentResponsibleId: $currentResponsibleId
        );
    }

    public function toArray(): array
    {
        return array_merge([
            'name' => $this->name,
            'email' => $this->email,
            'cell' => $this->phone,
            'user_id' => $this->userId,
        ], $this->address->toArray());
    }

    public function toCreateArray(): array
    {
        return $this->toArray();
    }

    public function toUpdateArray(): array
    {
        return $this->toArray();
    }

    public function getFormattedPhone(): string
    {
        $cleanPhone = preg_replace('/\D/', '', $this->phone);

        if (strlen($cleanPhone) === 11) {
            return sprintf(
                '(%s) %s-%s',
                substr($cleanPhone, 0, 2),
                substr($cleanPhone, 2, 5),
                substr($cleanPhone, 7)
            );
        }

        return $this->phone;
    }

    private function validate(): void
    {
        $this->clearValidationErrors();

        $this->validateRequired($this->name, 'Nome');
        $this->validateMinLength($this->name, 3, 'Nome');
        $this->validateMaxLength($this->name, 100, 'Nome');

        $this->validateEmail($this->email, 'E-mail');
        $this->validateUniqueEmail();

        $this->validatePhone();
        $this->validateUserId();

        $this->throwIfHasErrors();
    }

    private function validateUniqueEmail(): void
    {
        $specification = new UniqueResponsibleEmailSpecification();

        if (!$specification->isSatisfiedBy($this->email, $this->currentResponsibleId)) {
            $this->addValidationError($specification->getErrorMessage());
        }
    }

    private function validatePhone(): void
    {
        $this->validateRequired($this->phone, 'Telefone');

        $cleanPhone = preg_replace('/\D/', '', $this->phone);

        if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 11) {
            $this->addValidationError('Telefone deve ter 10 ou 11 dígitos');
        }

        if (strlen($cleanPhone) === 11 && !in_array(substr($cleanPhone, 2, 1), ['9'])) {
            $this->addValidationError('Celular deve começar com 9 após o DDD');
        }
    }

    private function validateUserId(): void
    {
        if ($this->userId === null) {
            return;
        }

        $this->validatePositiveInteger($this->userId, 'ID do usuário');

        if (!\App\Models\User::where('id', $this->userId)->exists()) {
            $this->addValidationError('Usuário não encontrado');
        }
    }
}
