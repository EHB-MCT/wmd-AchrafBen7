<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Funnel extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = ['id', 'user_id', 'step', 'step_order', 'timestamp'];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
