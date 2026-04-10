<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add composite index for history queries (filtering by user, month, year)
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'attendances_user_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_user_date_idx');
        });
    }
};
