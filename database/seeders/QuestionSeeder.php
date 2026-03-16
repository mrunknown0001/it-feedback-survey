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
            ['question_text' => 'How satisfied are you with the overall support provided by the IT team?',      'type' => 'rating', 'sort_order' => 1],
            ['question_text' => 'How would you rate the professionalism and attitude of the IT support staff?', 'type' => 'rating', 'sort_order' => 2],
            ['question_text' => 'How well did the IT support staff understand your issue or concern?',          'type' => 'rating', 'sort_order' => 3],

            // Response and Resolution
            ['question_text' => 'How would you rate the response time of IT support?',                         'type' => 'rating', 'sort_order' => 4],
            ['question_text' => 'How satisfied are you with the time it took to resolve your issue?',          'type' => 'rating', 'sort_order' => 5],
            ['question_text' => 'How effective was the solution provided to your problem?',                    'type' => 'rating', 'sort_order' => 6],

            // Communication
            ['question_text' => 'How clearly did the IT staff explain the issue and solution?',                'type' => 'rating', 'sort_order' => 7],
            ['question_text' => 'How satisfied are you with the communication and updates during the troubleshooting process?', 'type' => 'rating', 'sort_order' => 8],

            // Technical Competence
            ['question_text' => 'How would you rate the technical knowledge of the IT support staff?',         'type' => 'rating', 'sort_order' => 9],
            ['question_text' => 'How confident are you that the issue has been fully resolved?',               'type' => 'rating', 'sort_order' => 10],

            // Open-ended
            ['question_text' => 'Additional comment or suggestion (optional)',                                 'type' => 'text',   'sort_order' => 11],
        ];

        foreach ($questions as $question) {
            Question::create([...$question, 'is_active' => true]);
        }
    }
}
