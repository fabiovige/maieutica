<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checklist extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['kid_id', 'level', 'status', 'description', 'created_by', 'updated_by', 'deleted_by'];

    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }
}
