<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use SoftDeletes;

    protected $table = 'feedbacks';

    protected $fillable = ['respondent_name', 'position', 'issue_type_id', 'issue_description', 'overall_rating'];

    protected $casts = ['overall_rating' => 'float'];

    public function issueType(): BelongsTo
    {
        return $this->belongsTo(IssueType::class);
    }

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'feedback_agent');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(FeedbackResponse::class);
    }

    public function calculateOverallRating(): float
    {
        $ratingResponses = $this->responses()->whereNotNull('rating_value')->get();
        if ($ratingResponses->isEmpty()) {
            return 0;
        }
        return round($ratingResponses->avg('rating_value'), 2);
    }
}
