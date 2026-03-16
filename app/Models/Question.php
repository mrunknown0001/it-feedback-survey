<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = ['question_text', 'type', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function responses(): HasMany
    {
        return $this->hasMany(FeedbackResponse::class);
    }

    public function averageRating(): float
    {
        return round($this->responses()->whereNotNull('rating_value')->avg('rating_value') ?? 0, 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function isRating(): bool
    {
        return $this->type === 'rating';
    }
}
