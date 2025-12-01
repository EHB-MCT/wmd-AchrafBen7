<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insight extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = true;
    public const CREATED_AT = null;
    public const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'id',
        'user_id',
        'impulsivity_score',
        'hesitation_score',
        'premium_tendency',
        'night_user',
        'likely_to_book',
        'risk_churn',
    ];

    protected $casts = [
        'night_user' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
