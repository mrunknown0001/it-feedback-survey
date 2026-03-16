<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('created_at');
            $table->index('issue_type_id');
        });

        Schema::table('feedback_agent', function (Blueprint $table) {
            $table->index('agent_id');
        });

        Schema::table('feedback_responses', function (Blueprint $table) {
            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['created_at']);
            $table->dropIndex(['issue_type_id']);
        });

        Schema::table('feedback_agent', function (Blueprint $table) {
            $table->dropIndex(['agent_id']);
        });

        Schema::table('feedback_responses', function (Blueprint $table) {
            $table->dropIndex(['question_id']);
        });
    }
};
