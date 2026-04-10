<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('basic_salary', 12, 2)->nullable()->after('is_active');
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('basic_salary');
            $table->json('allowances')->nullable()->after('hourly_rate');
            $table->json('deductions')->nullable()->after('allowances');
            $table->string('bank_name')->nullable()->after('deductions');
            $table->string('bank_account')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'basic_salary',
                'hourly_rate',
                'allowances',
                'deductions',
                'bank_name',
                'bank_account',
                'bank_account_name',
            ]);
        });
    }
};
