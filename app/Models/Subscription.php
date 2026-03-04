<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan',
        'expires_at',
    ];

    protected $dates = [
        'expires_at',
    ];

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}