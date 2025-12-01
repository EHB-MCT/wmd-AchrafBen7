<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SearchQuery extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['id', 'user_id', 'query', 'filters', 'results_count', 'timestamp'];

    protected $casts = [
        'filters' => 'array',
        'timestamp' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
