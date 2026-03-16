<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Feedback;
use App\Models\FeedbackResponse;
use App\Models\IssueType;
use App\Models\Location;
use App\Models\Question;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function show()
    {
        $agents      = Agent::active()->orderBy('name')->get();
        $questions   = Question::active()->get();
        $issueTypes  = IssueType::active()->orderBy('sort_order')->orderBy('name')->get();
        $locations   = Location::active()->get();

        return view('feedback.form', compact('agents', 'questions', 'issueTypes', 'locations'));
    }

    public function store(Request $request)
    {
        $questions = Question::active()->get();

        $rules = [
            'respondent_name'   => 'required|string|max:255',
            'position'          => 'required|string|max:255',
            'agent_ids'         => 'required|array|min:1',
            'agent_ids.*'       => 'exists:agents,id',
            'issue_type_id'     => 'required|exists:issue_types,id',
            'issue_description' => 'nullable|string|max:2000',
            'location_ids'      => 'required|array|min:1',
            'location_ids.*'    => 'exists:locations,id',
        ];

        foreach ($questions as $question) {
            if ($question->type === 'rating') {
                $rules["responses.{$question->id}"] = 'required|integer|between:1,5';
            } else {
                $rules["responses.{$question->id}"] = 'nullable|string|max:2000';
            }
        }

        $validated = $request->validate($rules, [
            'respondent_name.required'  => 'Please enter your name.',
            'position.required'         => 'Please enter your position/designation.',
            'agent_ids.required'        => 'Please select at least one IT support agent.',
            'agent_ids.min'             => 'Please select at least one IT support agent.',
            'issue_type_id.required'    => 'Please select an issue / request type.',
            'issue_type_id.exists'      => 'The selected issue type is invalid.',
            'location_ids.required'     => 'Please select at least one location / area / department.',
            'location_ids.min'          => 'Please select at least one location / area / department.',
        ]);

        $feedback = Feedback::create([
            'respondent_name'   => $validated['respondent_name'],
            'position'          => $validated['position'],
            'issue_type_id'     => $validated['issue_type_id'],
            'issue_description' => $validated['issue_description'] ?? null,
            'overall_rating'    => 0,
        ]);

        $feedback->agents()->attach($validated['agent_ids']);
        $feedback->locations()->attach($validated['location_ids']);

        $responses   = $validated['responses'] ?? [];
        $ratingSum   = 0;
        $ratingCount = 0;

        foreach ($questions as $question) {
            $value = $responses[$question->id] ?? null;

            FeedbackResponse::create([
                'feedback_id'  => $feedback->id,
                'question_id'  => $question->id,
                'rating_value' => $question->type === 'rating' ? (int) $value : null,
                'text_value'   => $question->type === 'text' ? $value : null,
            ]);

            if ($question->type === 'rating' && $value !== null) {
                $ratingSum += (int) $value;
                $ratingCount++;
            }
        }

        $overallRating = $ratingCount > 0 ? round($ratingSum / $ratingCount, 2) : 0;
        $feedback->update(['overall_rating' => $overallRating]);

        return redirect()->route('feedback.thanks')->with('success', true);
    }

    public function thanks()
    {
        return view('feedback.thanks');
    }
}
