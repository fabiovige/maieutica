<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Competence extends Model
{
    use HasFactory;

    protected $fillable = ['level', 'domain_id', 'code', 'description', 'description_detail'];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function checklists(): BelongsToMany
    {
        return $this->belongsToMany(Checklist::class)->withPivot('note');
    }
}
