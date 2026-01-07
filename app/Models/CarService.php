<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarService extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'car_services';

    protected $fillable = [
        'car_id',
        'created_by',
        'kind',
        'status',
        'starts_at',
        'ends_at',
        'cost',
        'description',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cost' => 'decimal:2',
    ];

    // ---- Relations ----

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
