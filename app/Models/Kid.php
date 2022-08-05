<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Kid extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'birth_date', 'user_id', 'created_by', 'updated_by', 'deleted_by'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    public function getMonthsAttribute()
    {
        $birth_date = Carbon::createFromFormat('d/m/Y', $this->attributes['birth_date'])->format('Y-m-d');
        $now = Carbon::now();

        return ($now->diffInMonths($birth_date) == 0) ? 1 : $now->diffInMonths($birth_date);
    }
}
