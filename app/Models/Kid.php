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

    public function getBirthDateAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d', $value)->format('d/m/Y');
    }

    public function setBirthDateAttribute($value)
    {
        $this->attributes['birth_date'] = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
    }

    public static function boot() {
        parent::boot();
        self::deleting(function($kid) {
            $kid->checklists()->each(function($checklist) {
                $checklist->delete();
            });
        });
    }

}
