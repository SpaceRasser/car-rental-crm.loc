<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TestDrive extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'test_drives';

    protected $fillable = [
        'car_id',
        'client_id',
        'manager_id',
        'status',
        'scheduled_at',
        'duration_minutes',
        'started_at',
        'ended_at',
        'driving_experience_years',
        'phone',
        'email',
        'is_interested',
        'interest_score',
        'feedback',
        'notes',
        'cancel_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_minutes' => 'integer',
        'driving_experience_years' => 'integer',
        'is_interested' => 'boolean',
        'interest_score' => 'integer',
    ];

    // ---- Relations ----

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('test_drives')
            ->logOnly([
                'car_id','client_id','manager_id',
                'status',
                'scheduled_at','duration_minutes',
                'notes','cancel_reason',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
