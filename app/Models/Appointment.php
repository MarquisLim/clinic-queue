<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    public const STATUS_PENDING     = 'pending';
    public const STATUS_CHECKED_IN  = 'checked_in';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE        = 'done';
    public const STATUS_CANCELLED   = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CHECKED_IN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_DONE,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'patient_id','doctor_id','specialty_id',
        'slot_start','slot_len_min',
        'status','ticket_no',
        'late_cancel','complaint',
        'started_at','finished_at',
    ];

    protected $casts = [
        'slot_start'  => 'datetime',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        'late_cancel' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function scopeActive($q)
    {
        return $q->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_CHECKED_IN,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function scopeTodayForDoctor($q, int $doctorId)
    {
        return $q->where('doctor_id', $doctorId)
            ->whereDate('slot_start', now()->toDateString());
    }

    public function scopeUpcomingForPatient($q, int $patientId)
    {
        return $q->where('patient_id', $patientId)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CHECKED_IN])
            ->where('slot_start', '>=', now());
    }
}
