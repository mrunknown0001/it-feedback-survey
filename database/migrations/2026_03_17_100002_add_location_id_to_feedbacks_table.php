<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_location', function (Blueprint $table) {
            $table->foreignId('feedback_id')->constrained('feedbacks')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->primary(['feedback_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_location');
    }
};
