<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'event',
        'description',
        'properties',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    // ---- Relations ----

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
