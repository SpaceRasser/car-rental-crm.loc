<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarPhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'car_photos';

    protected $fillable = [
        'car_id',
        'path',
        'sort_order',
        'is_main',
        'alt',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_main' => 'boolean',
    ];

    // ---- Relations ----

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }
}
