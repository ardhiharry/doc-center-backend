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
        Schema::create('tp_5_activity_docs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable(false);
            $table->datetime('date')->nullable(false);
            $table->string('location')->nullable(false);
            $table->json('meet_of_person')->nullable(false);
            $table->json('agenda')->nullable(false);
            $table->json('activity')->nullable(false);
            $table->json('files')->nullable();
            $table->json('tags')->nullable(false);
            $table->foreignId('activity_id')
                ->nullable(false)
                ->constrained('tp_4_activities')
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
        Schema::dropIfExists('activity_docs');
    }
};
