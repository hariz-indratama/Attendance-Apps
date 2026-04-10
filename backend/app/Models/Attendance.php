<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_id',
        'shift_id',
        'location_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'clock_in_photo',
        'clock_out_photo',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime:H:i:s',
        'clock_out_time' => 'datetime:H:i:s',
        'clock_in_latitude' => 'decimal:8',
        'clock_in_longitude' => 'decimal:11',
        'clock_out_latitude' => 'decimal:8',
        'clock_out_longitude' => 'decimal:11',
    ];

    protected $appends = ['work_duration'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function getWorkDurationAttribute(): ?string
    {
        if ($this->clock_in_time && $this->clock_out_time) {
            $start = strtotime($this->clock_in_time);
            $end = strtotime($this->clock_out_time);
            $diff = $end - $start;
            $hours = floor($diff / 3600);
            $minutes = floor(($diff % 3600) / 60);
            return "{$hours}h {$minutes}m";
        }
        return null;
    }
}
