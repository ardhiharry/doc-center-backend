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
        Schema::create('tp_2_admin_docs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable(false);
            $table->string('file', 100)->nullable();
            $table->foreignId('project_id')
                ->nullable(false)
                ->constrained('tp_1_projects')
                ->onDelete('cascade');
            $table->foreignId('admin_doc_category_id')
                ->nullable(false)
                ->constrained('tm_admin_doc_categories')
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
        Schema::dropIfExists('admin_docs');
    }
};
