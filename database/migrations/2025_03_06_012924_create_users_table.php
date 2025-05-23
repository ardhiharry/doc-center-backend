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
        Schema::create('tm_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->nullable(false)->unique('users_username_unique');
            $table->string('password', 255)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->enum('role', ['SUPERADMIN', 'ADMIN', 'USER'])->default('USER');
            $table->text('token')->nullable();
            $table->boolean('is_process')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
