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
        'photo',
        'gender',
        'ethnicity',
        'responsible_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'months',
    ];

    protected $dates = [
        'birth_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public const GENDERS = [
        'M' => 'Masculino',
        'F' => 'Feminino',
    ];

    public const ETHNICITIES = [
        'branco' => 'Branco',
        'pardo' => 'Pardo',
        'negro' => 'Negro',
        'indigena' => 'Indígena',
        'amarelo' => 'Amarelo/Asiático',
        'multiracial' => 'Multiracial',
        'nao_declarado' => 'Não Declarado',
        'outro' => 'Outro',
    ];

    // Adicionando o Scope Local
    public function scopeForProfessional(Builder $query)
    {
        if (Auth::check() && Auth::user()->hasRole('professional')) {
            return $query->whereHas('professionals', function ($query) {
                $query->where('users.id', Auth::id());
            });
        }

        return $query;
    }

    // Relacionamento com o responsável (ROLE_PAIS)
    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id')->withDefault();
    }

    // Novo relacionamento many-to-many com professionals
    public function professionals()
    {
        return $this->belongsToMany(Professional::class, 'kid_professional');
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

    public function getAgeAttribute()
    {
        if (! $this->birth_date) {
            return null;
        }

        try {
            $birthDate = Carbon::parse($this->getRawOriginal('birth_date'));
            $now = Carbon::now();

            $years = $birthDate->diffInYears($now);
            $months = $birthDate->diffInMonths($now) % 12;

            if ($years > 0) {
                return $years.'a '.$months.'m';
            }

            return $months.' meses';
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getBirthDateAttribute($value)
    {
        if (! $value) {
            return null;
        }
        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setBirthDateAttribute($value)
    {
        if (! $value) {
            $this->attributes['birth_date'] = null;

            return;
        }

        try {
            if (str_contains($value, '/')) {
                $date = Carbon::createFromFormat('d/m/Y', $value);
            } else {
                $date = Carbon::parse($value);
            }
            $this->attributes['birth_date'] = $date->format('Y-m-d');
        } catch (\Exception $e) {
            $this->attributes['birth_date'] = null;
        }
    }

    public function getMonthsAttribute()
    {
        if (! $this->birth_date) {
            return null;
        }

        try {
            $birthDate = Carbon::parse($this->getRawOriginal('birth_date'));

            return $birthDate->diffInMonths(Carbon::now());
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getFullNameMonthsAttribute()
    {
        if (! $this->birth_date) {
            return null;
        }

        try {
            $birthDate = Carbon::parse($this->getRawOriginal('birth_date'));
            $month = $birthDate->diffInMonths(Carbon::now());
            $formattedDate = $birthDate->format('d/m/Y');

            return 'Cod. '.$this->id.' - '.' Nascido em: '.$formattedDate.' ('.$month.' meses)';
        } catch (\Exception $e) {
            return 'Data inválida';
        }
    }

    public function getInitialsAttribute()
    {
        $name = trim($this->name);
        if (empty($name)) {
            return 'NA';
        }

        $words = array_filter(explode(' ', $name));

        if (count($words) >= 2) {
            // Primeira letra do primeiro e último nome
            return mb_strtoupper(
                mb_substr($words[0], 0, 1).
                mb_substr(end($words), 0, 1)
            );
        }

        // Se for apenas um nome, pega as duas primeiras letras
        return mb_strtoupper(mb_substr($name, 0, 2));
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
        } elseif (auth()->user()->hasRole('professional')) {
            $query->where(function ($query) {
                $query->whereHas('professionals', function ($q) {
                    $q->where('users.id', auth()->user()->id);
                })->orWhere('created_by', auth()->user()->id);
            })
                ->with(['professionals', 'responsible', 'checklists']);
        } elseif (auth()->user()->hasRole('pais')) {
            $query->where('responsible_id', auth()->user()->id)
                ->with(['professionals', 'responsible', 'checklists']);
        }

        return $query->get();
    }
}
