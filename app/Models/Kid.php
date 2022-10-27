<?php

namespace App\Models;

use Illuminate\Support\Carbon;

class Kid extends BaseModel
{
    protected $fillable = [
        'name',
        'birth_date',
        'user_id',
        'responsible_id',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responsible()
    {
        return $this->belongsTo(Responsible::class);
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    public function planes()
    {
        return $this->hasMany(Plane::class);
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
