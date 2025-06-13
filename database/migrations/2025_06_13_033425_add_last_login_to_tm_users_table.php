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
        Schema::table('tm_users', function (Blueprint $table) {
            $table->timestamp('last_login')->nullable()->after('is_process');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tm_users', function (Blueprint $table) {
            $table->dropColumn('last_login');
        });
    }
};
