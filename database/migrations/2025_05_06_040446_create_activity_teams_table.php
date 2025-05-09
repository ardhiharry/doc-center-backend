<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tr_activity_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')
                ->nullable(false)
                ->constrained('tp_4_activities');
            $table->foreignId('user_id')
                ->nullable(false)
                ->constrained('tm_users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_teams');
    }
};
