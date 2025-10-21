<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('epf_no')->nullable()->after('employee_id');
            $table->decimal('basic', 10, 2)->nullable()->after('epf_no');
            $table->decimal('budget_allowance', 10, 2)->nullable()->after('basic');
            $table->decimal('transport_allowance', 10, 2)->nullable()->after('budget_allowance');
            $table->decimal('attendance_allowance', 10, 2)->nullable()->after('transport_allowance');
            $table->decimal('phone_allowance', 10, 2)->nullable()->after('attendance_allowance');
            $table->decimal('car_allowance', 10, 2)->nullable()->after('phone_allowance');
            $table->decimal('production_bonus', 10, 2)->nullable()->after('car_allowance');
            $table->decimal('stamp_duty', 10, 2)->nullable()->after('production_bonus');
            $table->index('epf_no');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['epf_no']);
            $table->dropColumn([
                'epf_no',
                'basic',
                'budget_allowance',
                'transport_allowance',
                'attendance_allowance',
                'phone_allowance',
                'car_allowance',
                'production_bonus',
                'stamp_duty',
            ]);
        });
    }
};
