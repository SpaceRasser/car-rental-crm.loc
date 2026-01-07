<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'user_id',
        'created_by',
        'first_name',
        'last_name',
        'middle_name',
        'phone',
        'email',
        'driver_license_number',
        'driver_license_issued_at',
        'driver_license_expires_at',
        'birth_date',
        'reliability_status',
        'is_verified',
        'notes',
    ];

    protected $casts = [
        'driver_license_issued_at' => 'date',
        'driver_license_expires_at' => 'date',
        'birth_date' => 'date',
        'is_verified' => 'boolean',
    ];

    // ---- Relations ----

    // если у клиента есть аккаунт для входа
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // кто создал карточку (менеджер/админ)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class, 'client_id');
    }

    public function testDrives()
    {
        return $this->hasMany(TestDrive::class, 'client_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('clients')
            ->logOnly([
                'user_id','created_by',
                'first_name','last_name','middle_name',
                'phone','email',
                'driver_license_number','driver_license_issued_at','driver_license_expires_at',
                'birth_date',
                'reliability_status','is_verified','notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ---- Helpers ----
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ])));
    }
}
