<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = [
        'doctor_id','date','start_time','end_time','slot_len_min',
        'breaks','is_closed','closed_reason',
    ];

    protected $casts = [
        'date'       => 'date',
        'breaks'     => 'array',
        'is_closed'  => 'boolean',
        'start_time' => 'string',
        'end_time'   => 'string',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function scopeOpen($q)
    {
        return $q->where('is_closed', false);
    }

    public function scopeForDate($q, $date)
    {
        return $q->whereDate('date', $date);
    }
}
