<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentReplacement extends Model
{
    public const ACTION_CANCELLATION = 'c';

    public const ACTION_REPLACEMENT = 'r';

    protected $fillable = [
        'appointment_id',
        'action',
        'google_event_id',
        'old_patient_name',
        'old_patient_email',
        'old_patient_phone',
        'old_reason',
        'old_professional_id',
        'new_patient_name',
        'new_patient_email',
        'new_patient_phone',
        'new_reason',
        'new_professional_id',
        'cancellation_reason',
        'performed_by',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function oldProfessional(): BelongsTo
    {
        return $this->belongsTo(Professional::class, 'old_professional_id');
    }

    public function newProfessional(): BelongsTo
    {
        return $this->belongsTo(Professional::class, 'new_professional_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
