<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Rental extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'rentals';
    protected $guarded = [];


    protected $fillable = [
        'car_id',
        'client_id',
        'manager_id',
        'status',
        'starts_at',
        'ends_at',
        'picked_up_at',
        'returned_at',
        'daily_price',
        'deposit_amount',
        'days_count',
        'base_total',
        'discount_total',
        'penalty_total',
        'grand_total',
        'mileage_start_km',
        'mileage_end_km',
        'fuel_start_percent',
        'fuel_end_percent',
        'contract_number',
        'contract_pdf_path',
        'purpose',
        'notes',
        'group_uuid',
        'is_trusted_person',
        'trusted_person_name',
        'trusted_person_phone',
        'trusted_person_license_number',
        'cancel_reason',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'returned_at' => 'datetime',

        'daily_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'base_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'penalty_total' => 'decimal:2',
        'grand_total' => 'decimal:2',

        'days_count' => 'integer',
        'mileage_start_km' => 'integer',
        'mileage_end_km' => 'integer',
        'fuel_start_percent' => 'integer',
        'fuel_end_percent' => 'integer',
        'is_trusted_person' => 'boolean',
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

    public function payments()
    {
        return $this->hasMany(Payment::class, 'rental_id');
    }

    public function extras()
    {
        // rental_extras — это таблица с доп. полями
        return $this->belongsToMany(Extra::class, 'rental_extras', 'rental_id', 'extra_id')
            ->withPivot(['pricing_type', 'price', 'qty'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    protected static function booted(): void
    {
        static::saving(function (Rental $rental) {
            if (!$rental->starts_at || !$rental->ends_at) {
                return;
            }

            $from = Carbon::parse($rental->starts_at);
            $to   = Carbon::parse($rental->ends_at);

            if ($to->lessThanOrEqualTo($from)) {
                return;
            }

            $minutes = max(0, $from->diffInMinutes($to));
            $days = max(1, (int) ceil($minutes / 1440));

            $daily = (float) ($rental->daily_price ?? 0);
            $base  = round($days * $daily, 2);

            $discount = (float) ($rental->discount_total ?? 0);
            $penalty  = (float) ($rental->penalty_total ?? 0);

            $grand = round(max($base - $discount + $penalty, 0), 2);

            $rental->days_count  = $days;
            $rental->base_total  = $base;
            $rental->grand_total = $grand;
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('rentals')
            ->logOnly([
                'car_id','client_id','manager_id','status',
                'starts_at','ends_at',
                'daily_price','deposit_amount',
                'days_count','base_total','discount_total','penalty_total','grand_total',
                'notes','cancel_reason',
                'group_uuid',
                'is_trusted_person',
                'trusted_person_name','trusted_person_phone','trusted_person_license_number',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }


    // ---- Helpers ----

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, ['confirmed', 'active', 'overdue'], true);
    }
}
