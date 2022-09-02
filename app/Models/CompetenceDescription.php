<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CompetenceDescription extends Model
{
    use HasFactory;

    protected $fillable = ['level', 'competence_id', 'code', 'description', 'description_detail'];

    public function competence(): BelongsTo
    {
        return $this->belongsTo(Competence::class);
    }

    public function checklists(): BelongsToMany
    {
        return $this->belongsToMany(Checklist::class);
    }
}
