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
        Schema::create('tp_4_activities', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable(false);
            $table->date('start_date')->nullable(false);
            $table->date('end_date')->nullable(false);
            $table->foreignId('activity_category_id')
                ->nullable(false)
                ->constrained('tp_3_activity_categories')
                ->onDelete('cascade');
            $table->foreignId('project_id')
                ->nullable(false)
                ->constrained('tp_1_projects')
                ->onDelete('cascade');
            $table->foreignId('author_id')
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
        Schema::dropIfExists('activities');
    }
};
