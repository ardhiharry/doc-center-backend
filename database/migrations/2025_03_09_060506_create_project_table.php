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
        Schema::create('tp_1_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->string('code', 10)->nullable(false);
            $table->string('client', 100)->nullable(false);
            $table->string('ppk', 100)->nullable(false);
            $table->json('support_teams')->nullable(false);
            $table->bigInteger('value')->nullable(false);
            $table->enum('status', ['WAITING', 'ON PROGRESS', 'CLOSED'])->default('WAITING');
            $table->float('progress')->default(0);
            $table->foreignId('company_id')->nullable(false)->constrained('tm_companies')->onDelete('cascade');
            $table->foreignId('project_leader_id')->nullable(false)->constrained('tm_users')->onDelete('cascade');
            $table->date('start_date')->nullable(false);
            $table->date('end_date')->nullable(false);
            $table->date('maintenance_date')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
