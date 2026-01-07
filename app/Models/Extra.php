<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Extra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'extras';
    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function rentals()
    {
        return $this->belongsToMany(Rental::class, 'rental_extras', 'extra_id', 'rental_id')
            ->withPivot(['pricing_type', 'price', 'qty'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }
}
