<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // Service Quality
            ['question_text' => 'How satisfied are you with the overall support provided by the HR team?',       'type' => 'rating', 'sort_order' => 1],
            ['question_text' => 'How would you rate the professionalism and attitude of the HR personnel?',     'type' => 'rating', 'sort_order' => 2],
            ['question_text' => 'How well did the HR personnel understand your concern or request?',            'type' => 'rating', 'sort_order' => 3],

            // Response and Resolution
            ['question_text' => 'How would you rate the response time of HR?',                                  'type' => 'rating', 'sort_order' => 4],
            ['question_text' => 'How satisfied are you with the time it took to resolve your concern?',         'type' => 'rating', 'sort_order' => 5],
            ['question_text' => 'How effective was the resolution provided to your request?',                   'type' => 'rating', 'sort_order' => 6],

            // Communication
            ['question_text' => 'How clearly did the HR personnel explain the process and outcome?',            'type' => 'rating', 'sort_order' => 7],
            ['question_text' => 'How satisfied are you with the communication and updates throughout the process?', 'type' => 'rating', 'sort_order' => 8],

            // Confidentiality & Competence
            ['question_text' => 'How confident are you that your concern was handled with appropriate confidentiality?', 'type' => 'rating', 'sort_order' => 9],
            ['question_text' => 'How confident are you that your concern has been fully addressed?',            'type' => 'rating', 'sort_order' => 10],

            // Open-ended
            ['question_text' => 'Additional comment or suggestion (optional)',                                  'type' => 'text',   'sort_order' => 11],
        ];

        foreach ($questions as $question) {
            Question::firstOrCreate(
                ['question_text' => $question['question_text']],
                array_merge($question, ['is_active' => true])
            );
        }
    }
}
