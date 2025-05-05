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
        Schema::create('tr_project_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->nullable(false)
                ->constrained('tp_1_projects')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->nullable(false)
                ->constrained('tm_users')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
