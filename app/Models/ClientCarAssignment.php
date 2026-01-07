<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCarAssignment extends Model
{
    use HasFactory;

    protected $table = 'client_car_assignments';

    protected $fillable = [
        'client_id',
        'car_id',
        'relation_type',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }
}
