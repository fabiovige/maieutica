<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Models\Checklist;

class ChecklistData extends AbstractValueObject
{
    public function __construct(
        public readonly int $level,
        public readonly int $kidId,
        public readonly string $situation,
        public readonly ?string $description = null
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            level: (int) $data['level'],
            kidId: (int) $data['kid_id'],
            situation: $data['situation'] ?? 'a',
            description: $data['description'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'level' => $this->level,
            'kid_id' => $this->kidId,
            'situation' => $this->situation,
            'description' => $this->description,
        ];
    }

    public function toCreateArray(): array
    {
        return $this->toArray();
    }

    public function getLevelName(): string
    {
        return Checklist::LEVEL[$this->level] ?? 'Nível Desconhecido';
    }

    public function getSituationName(): string
    {
        return Checklist::SITUATION[$this->situation] ?? 'Situação Desconhecida';
    }

    public function isOpen(): bool
    {
        return $this->situation === 'a';
    }

    public function isClosed(): bool
    {
        return $this->situation === 'f';
    }

    private function validate(): void
    {
        $this->clearValidationErrors();

        $this->validateLevel();
        $this->validateKidId();
        $this->validateSituation();
        $this->validateDescription();

        $this->throwIfHasErrors();
    }

    private function validateLevel(): void
    {
        $allowedLevels = array_keys(Checklist::LEVEL);
        $allowedLevelsInt = array_map('intval', $allowedLevels);

        if (!in_array($this->level, $allowedLevelsInt, true)) {
            $this->addValidationError('Nível deve ser: ' . implode(', ', $allowedLevels));
        }
    }

    private function validateKidId(): void
    {
        $this->validatePositiveInteger($this->kidId, 'ID da criança');

        if (!\App\Models\Kid::where('id', $this->kidId)->exists()) {
            $this->addValidationError('Criança não encontrada');
        }
    }

    private function validateSituation(): void
    {
        $allowedSituations = array_keys(Checklist::SITUATION);

        if (!in_array($this->situation, $allowedSituations, true)) {
            $this->addValidationError('Situação deve ser: ' . implode(' ou ', $allowedSituations));
        }
    }

    private function validateDescription(): void
    {
        if ($this->description !== null) {
            $this->validateMaxLength($this->description, 1000, 'Descrição');
        }
    }
}
