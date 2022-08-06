<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checklist extends Model
{
    use HasFactory;

    use SoftDeletes;

    const LEVEL = [
        '1' => 'Nível 1',
        '2' => 'Nível 2',
        '3' => 'Nível 3',
        '4' => 'Nível 4',
    ];

    const SITUATION = [
        'a' => 'Aberto',
        'f' => 'Fechado'
    ];

    protected $fillable = ['level', 'kid_id', 'situation', 'description', 'created_by', 'updated_by', 'deleted_by'];

    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }
}
