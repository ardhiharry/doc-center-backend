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
        Schema::create('tp_6_chartered_accountants', function (Blueprint $table) {
            $table->id();
            $table->date('application_date');
            $table->string('classification');
            $table->float('total');
            $table->text('description');
            $table->json('images')->nullable();
            $table->foreignId('applicant_id')->constrained('tm_users');
            $table->foreignId('project_id')->nullable()->constrained('tp_1_projects');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tp_6_chartered_accountants');
    }
};
