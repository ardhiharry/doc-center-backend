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
        Schema::create('activity_docs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable(false);
            $table->json('files')->nullable();
            $table->text('description')->nullable();
            $table->json('tags')->nullable(false);
            $table->foreignId('activity_id')
                ->unique()
                ->nullable(false)
                ->constrained('activities')
                ->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
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
