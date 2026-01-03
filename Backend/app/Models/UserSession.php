<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserSession extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user_sessions';

    protected $fillable = [
        'id',
        'user_id',
        'start_time',
        'end_time',
        'duration_seconds',
        'platform',
        'network_type',
        'battery_level',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'session_id');
    }
}
 