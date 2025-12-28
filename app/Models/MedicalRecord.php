<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MedicalRecord extends BaseModel
{
    protected $fillable = [
        'patient_id',
        'patient_type',
        'session_date',
        'complaint',
        'objective_technique',
        'evolution_notes',
        'referral_closure',
        'parent_id',
        'version',
        'is_current_version',
        'html_content',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'session_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'session_date' => 'date',
        'is_current_version' => 'boolean',
    ];

    /**
     * Polymorphic relationship to patient (Kid or User)
     */
    public function patient()
    {
        return $this->morphTo();
    }

    /**
     * Audit trail relationships
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault();
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->withDefault();
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by')->withDefault();
    }

    /**
     * Versioning relationships
     */
    public function parent()
    {
        return $this->belongsTo(MedicalRecord::class, 'parent_id');
    }

    public function versions()
    {
        return $this->hasMany(MedicalRecord::class, 'parent_id')->orderBy('version', 'desc');
    }

    /**
     * Date handling - Brazilian format (d/m/Y)
     * This accessor provides formatted date for display
     */
    public function getSessionDateFormattedAttribute()
    {
        if (!$this->attributes['session_date']) {
            return null;
        }

        try {
            return Carbon::parse($this->attributes['session_date'])->format('d/m/Y');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setSessionDateAttribute($value)
    {
        if (!$value) {
            $this->attributes['session_date'] = null;
            return;
        }

        try {
            if (str_contains($value, '/')) {
                // Brazilian format d/m/Y
                $date = Carbon::createFromFormat('d/m/Y', $value);
            } else {
                // ISO format Y-m-d
                $date = Carbon::parse($value);
            }
            $this->attributes['session_date'] = $date->format('Y-m-d');
        } catch (\Exception $e) {
            $this->attributes['session_date'] = null;
        }
    }

    /**
     * Display accessors
     */
    public function getPatientNameAttribute()
    {
        return $this->patient ? $this->patient->name : 'N/A';
    }

    public function getPatientTypeNameAttribute()
    {
        if (str_contains($this->patient_type, 'Kid')) {
            return 'Criança';
        }

        if (str_contains($this->patient_type, 'User')) {
            return 'Adulto';
        }

        return 'Desconhecido';
    }

    /**
     * Scope para filtrar medical records do profissional autenticado
     * Professional can view records for their assigned patients (Kids and Users)
     */
    public function scopeForAuthProfessional(Builder $query)
    {
        $professional = auth()->user()->professional->first();

        if (!$professional) {
            return $query->whereRaw('1 = 0'); // Return no results
        }

        return $query->where(function ($q) use ($professional) {
            // Records for assigned Kids
            $q->where(function ($subQ) use ($professional) {
                $subQ->where('patient_type', Kid::class)
                     ->whereIn('patient_id', $professional->kids()->pluck('kids.id'));
            })
            // Records for assigned User patients
            ->orWhere(function ($subQ) use ($professional) {
                $subQ->where('patient_type', User::class)
                     ->whereIn('patient_id', $professional->patients()->pluck('users.id'));
            });
        });
    }

    /**
     * Scope para filtrar medical records do paciente autenticado
     * Patient can only view their own medical records
     */
    public function scopeForAuthPatient(Builder $query)
    {
        $userId = auth()->id();

        return $query->where('patient_type', User::class)
                     ->where('patient_id', $userId);
    }

    /**
     * Scope para filtrar por paciente específico
     */
    public function scopeForPatient(Builder $query, $patientId, $patientType)
    {
        return $query->where('patient_id', $patientId)
                     ->where('patient_type', $patientType);
    }

    /**
     * Scope para versão atual apenas
     */
    public function scopeCurrentVersion(Builder $query)
    {
        return $query->where('is_current_version', true);
    }

    /**
     * Scope para histórico (versões antigas)
     */
    public function scopeHistory(Builder $query)
    {
        return $query->where('is_current_version', false)->orderBy('version', 'desc');
    }

    /**
     * Método para obter todas as versões (incluindo esta)
     */
    public function getAllVersions()
    {
        $parentId = $this->parent_id ?? $this->id;
        
        return MedicalRecord::where('id', $parentId)
                            ->orWhere('parent_id', $parentId)
                            ->orderBy('version', 'desc')
                            ->get();
    }

    /**
     * Método para obter versão mais recente
     */
    public function getLatestVersion()
    {
        $parentId = $this->parent_id ?? $this->id;
        
        return MedicalRecord::where(function($q) use ($parentId) {
                                $q->where('id', $parentId)
                                  ->orWhere('parent_id', $parentId);
                            })
                            ->where('is_current_version', true)
                            ->first();
    }

    /**
     * Método para obter a versão original (v1)
     */
    public function getOriginalVersion()
    {
        if ($this->parent_id) {
            return MedicalRecord::find($this->parent_id);
        }

        return $this;
    }
}
