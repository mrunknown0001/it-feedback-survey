<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agent extends Model
{
    protected $fillable = ['name', 'employee_id', 'department', 'email', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function feedbacks(): BelongsToMany
    {
        return $this->belongsToMany(Feedback::class, 'feedback_agent');
    }

    public function averageRating(): float
    {
        return round($this->feedbacks()->avg('overall_rating') ?? 0, 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
