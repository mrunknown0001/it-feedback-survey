<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_agent', function (Blueprint $table) {
            $table->foreignId('feedback_id')->constrained('feedbacks')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->primary(['feedback_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_agent');
    }
};
