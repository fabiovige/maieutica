<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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

    // Adicionando o Scope Local
    public function scopeForProfessional(Builder $query)
    {
        if (Auth::check() && Auth::user()->hasRole('professional')) {
            return $query->where('profession_id', Auth::id());
        }

        return $query;
    }


    // Relacionamento com o responsável (ROLE_PAIS)
    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    // Relacionamento com o professional (ROLE_PROFESSION)
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
        $query = Kid::query();

        if (auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin')) {
            $query->with(['professional', 'responsible', 'checklists']);
        } else if (auth()->user()->hasRole('professional')) {
            $query->where('profession_id', auth()->user()->id)
                ->whereOr('created_by', auth()->user()->id)
                ->with(['professional', 'responsible', 'checklists']);
        } else if (auth()->user()->hasRole('pais')) {
            $query->where('responsible_id', auth()->user()->id)
                ->with(['professional', 'responsible', 'checklists']);
        }

        return $query->get();
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
