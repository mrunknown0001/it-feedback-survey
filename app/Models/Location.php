<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    protected $fillable = ['name', 'code', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function feedbacks(): BelongsToMany
    {
        return $this->belongsToMany(Feedback::class, 'feedback_location');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
