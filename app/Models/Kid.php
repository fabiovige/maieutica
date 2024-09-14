<?php

namespace App\Models;

use Illuminate\Support\Carbon;

class Kid extends BaseModel
{
    protected $fillable = [
        'name',
        'birth_date',
        'profession_id',
        'responsible_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'months',
    ];

    // Relacionamento com o responsável (ROLE_PAIS)
    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    // Relacionamento com o profissional (ROLE_PROFESSION)
    public function professional()
    {
        return $this->belongsTo(User::class, 'profession_id');
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

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($kid) {
            $kid->checklists()->each(function ($checklist) {
                $checklist->delete();
            });
        });
    }

    public static function getKids()
    {
        /*
        $data = null;
        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
            $data = Kid::with('re')->select('id', 'name', 'birth_date', 'user_id');
        } else {
            $data = Kid::select('id', 'name', 'birth_date', 'user_id');
            $data->where('created_by', '=', auth()->user()->id);
            $data->orWhere('user_id', '=', auth()->user()->id);

        }*/

        //$data = Kid::select('id', 'name', 'birth_date', 'profession_id', 'responsible_id');

        //$data = Kid::with(['professional', 'responsible', 'checklists']); // Carregando as relações necessárias
        $data = Kid::all(); // Carregando as relações necessárias

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

        return $month.' meses - '.$this->birth_date.' - Cod. '.$this->id;
    }
}
