<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Kid extends Model
{
    protected $fillable = [
        'name',
        'birth_date',
        'responsible_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'months',
        'photo'
    ];

    protected $casts = [
        'birth_date' => 'datetime',
    ];

    // Adicionando o Scope Local
    public function scopeForProfessional(Builder $query)
    {
        if (Auth::check() && Auth::user()->hasRole('professional')) {
            return $query->whereHas('professionals', function ($q) {
                $q->where('users.id', Auth::id());
            });
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

    // Se você precisa de um checklist "atual" baseado em uma regra (como o mais recente)
    public function currentChecklist()
    {
        return $this->hasOne(Checklist::class)->latest();
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
            $query->with(['professionals', 'responsible', 'checklists']);
        } else if (auth()->user()->hasRole('professional')) {
            $query->whereHas('professionals', function ($q) {
                $q->where('users.id', auth()->id());
            })
            ->orWhere('created_by', auth()->user()->id)
            ->with(['professionals', 'responsible', 'checklists']);
        } else if (auth()->user()->hasRole('pais')) {
            $query->where('responsible_id', auth()->user()->id)
                ->with(['professionals', 'responsible', 'checklists']);
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

        return 'Cod. '. $this->id . ' - ' . ' Nascido em: ' . $this->birth_date . ' (' . $month . ' meses)';
    }

    /**
     * Os profissionais associados a esta criança
     */
    public function professionals()
    {
        return $this->belongsToMany(User::class, 'kid_professional')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * O profissional principal desta criança
     */
    public function primaryProfessional()
    {
        return $this->belongsToMany(User::class, 'kid_professional')
            ->withPivot('is_primary')
            ->wherePivot('is_primary', true)
            ->first();
    }

    /**
     * Atribui profissionais a esta criança
     */
    public function assignProfessionals(array $professionalIds, $primaryProfessionalId = null)
    {
        $sync = [];
        foreach ($professionalIds as $id) {
            $sync[$id] = ['is_primary' => ($id == $primaryProfessionalId)];
        }

        return $this->professionals()->sync($sync);
    }

    /**
     * Adiciona um profissional a esta criança
     */
    public function addProfessional($professionalId, $isPrimary = false)
    {
        return $this->professionals()->attach($professionalId, [
            'is_primary' => $isPrimary
        ]);
    }

    /**
     * Remove um profissional desta criança
     */
    public function removeProfessional($professionalId)
    {
        return $this->professionals()->detach($professionalId);
    }
}
