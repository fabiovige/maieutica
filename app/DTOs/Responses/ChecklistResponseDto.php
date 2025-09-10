<?php

declare(strict_types=1);

namespace App\DTOs\Responses;

use App\Models\Checklist;

class ChecklistResponseDto extends AbstractResponseDto
{
    public function __construct(
        public int $id,
        public int $level,
        public string $levelName,
        public string $situation,
        public string $situationName,
        public ?string $description,
        public float $percentage,
        public int $kidId,
        public string $kidName,
        public array $competences,
        public array $statusEvaluation,
        public ?string $createdAt,
        public ?string $updatedAt
    ) {
    }

    public static function fromModel(Checklist $checklist): self
    {
        $competences = $checklist->competences->map(function ($competence) {
            return [
                'id' => $competence->id,
                'code' => $competence->code,
                'description' => $competence->description,
                'domain' => $competence->domain?->name,
                'level' => $competence->level?->name,
                'note' => $competence->pivot->note ?? null,
            ];
        })->toArray();

        return new self(
            id: $checklist->id,
            level: $checklist->level,
            levelName: $checklist->getLevelName(),
            situation: $checklist->situation,
            situationName: $checklist->getSituationName(),
            description: $checklist->description,
            percentage: $checklist->calculatePercentage(),
            kidId: $checklist->kid_id,
            kidName: $checklist->kid?->name ?? 'N/A',
            competences: $competences,
            statusEvaluation: $checklist->getStatusAvaliation(),
            createdAt: $checklist->created_at?->format('d/m/Y H:i'),
            updatedAt: $checklist->updated_at?->format('d/m/Y H:i')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'level_name' => $this->levelName,
            'situation' => $this->situation,
            'situation_name' => $this->situationName,
            'description' => $this->description,
            'percentage' => $this->percentage,
            'percentage_formatted' => number_format($this->percentage, 1) . '%',
            'kid' => [
                'id' => $this->kidId,
                'name' => $this->kidName,
            ],
            'competences' => $this->competences,
            'competences_count' => count($this->competences),
            'status_evaluation' => $this->statusEvaluation,
            'is_open' => $this->situation === 'a',
            'is_closed' => $this->situation === 'f',
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function toMinimalArray(): array
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'level_name' => $this->levelName,
            'percentage' => $this->percentage,
            'percentage_formatted' => number_format($this->percentage, 1) . '%',
            'situation_name' => $this->situationName,
            'competences_count' => count($this->competences),
            'kid_name' => $this->kidName,
            'created_at' => $this->createdAt,
        ];
    }

    public function toProgressArray(): array
    {
        return [
            'id' => $this->id,
            'level_name' => $this->levelName,
            'percentage' => $this->percentage,
            'status_evaluation' => $this->statusEvaluation,
            'created_at' => $this->createdAt,
        ];
    }
}