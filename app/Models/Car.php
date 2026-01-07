<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Car extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cars';

    protected $fillable = [
        'brand',
        'model',
        'year',
        'color',
        'vin',
        'plate_number',
        'fuel_type',
        'transmission',
        'mileage_km',
        'daily_price',
        'deposit_amount',
        'status',
        'description',
        'last_service_at',
        'is_active',
    ];

    protected $casts = [
        'year' => 'integer',
        'mileage_km' => 'integer',
        'daily_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'last_service_at' => 'date',
    ];

    // ---- Relations ----

    public function photos()
    {
        return $this->hasMany(CarPhoto::class, 'car_id');
    }

    public function mainPhoto()
    {
        return $this->hasOne(CarPhoto::class, 'car_id')->where('is_main', true);
    }

    public function services()
    {
        return $this->hasMany(CarService::class, 'car_id');
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class, 'car_id');
    }

    public function testDrives()
    {
        return $this->hasMany(TestDrive::class, 'car_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('cars')
            ->logOnly([
                'brand','model','year','color',
                'vin','plate_number',
                'fuel_type','transmission','mileage_km',
                'daily_price','deposit_amount',
                'status','description','last_service_at','is_active',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
