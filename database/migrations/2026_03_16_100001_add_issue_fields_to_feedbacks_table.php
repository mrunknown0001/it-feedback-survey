<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->foreignId('issue_type_id')->nullable()->constrained('issue_types')->nullOnDelete()->after('position');
            $table->text('issue_description')->nullable()->after('issue_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('issue_type_id');
            $table->dropColumn('issue_description');
        });
    }
};
