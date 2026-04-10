<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->unique()->nullable()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->string('position')->nullable()->after('phone');
            $table->string('department')->nullable()->after('position');
            $table->foreignId('shift_id')->nullable()->after('department')->constrained('shifts')->onDelete('set null');
            $table->foreignId('location_id')->nullable()->after('shift_id')->constrained('locations')->onDelete('set null');
            $table->enum('role', ['admin', 'manager', 'employee'])->default('employee')->after('location_id');
            $table->boolean('is_active')->default(true)->after('role');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'phone',
                'position',
                'department',
                'shift_id',
                'location_id',
                'role',
                'is_active',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
