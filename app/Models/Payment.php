<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payments';
    protected $guarded = [];

    protected $fillable = [
        'rental_id',
        'created_by',
        'kind',
        'provider',
        'status',
        'amount',
        'currency',
        'payment_reference',
        'external_id',
        'paid_at',
        'refunded_at',
        'provider_payload',
        'fail_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'provider_payload' => 'array', // json -> array
    ];

    // ---- Relations ----

    public function rental()
    {
        return $this->belongsTo(Rental::class, 'rental_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('payments')
            ->logOnly([
                'rental_id',
                'amount','currency',
                'provider','status',
                'payment_reference',
                'external_id',
                'paid_at',
                'created_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ---- Helpers ----

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid' && $this->paid_at !== null;
    }
}
