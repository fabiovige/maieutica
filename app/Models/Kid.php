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
        'deleted_by',
        'months'
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

    public static function getKids()
    {
        $data = null;
        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
            $data = Kid::with('user')->select('id', 'name', 'birth_date', 'user_id', 'responsible_id');
        } else {
            $data = Kid::select('id', 'name', 'birth_date', 'user_id', 'responsible_id');
            $data->where('created_by', '=', auth()->user()->id);
            $data->orWhere('user_id', '=', auth()->user()->id);

            $responsible = Responsible::where("user_id", '=', auth()->user()->id)->first();
            if ($responsible) {
                $data->orWhere('responsible_id',  '=', $responsible->id);
            }
        }
        return $data;
    }

    public function getMonthsAttribute()
    {
        $now = Carbon::now();
        $dt = Carbon::createFromFormat('d/m/Y', $this->birth_date)->format('Y-m-d');
        return $now->diffInMonths($dt);
    }


    public function getFullNameMonthsAttribute()
    {
        $now = Carbon::now();
        $dt = Carbon::createFromFormat('d/m/Y', $this->birth_date)->format('Y-m-d');
        $month = $now->diffInMonths($dt);

        return $month . ' meses - ' . $this->birth_date . ' - Cod. ' . $this->id;
    }

}
