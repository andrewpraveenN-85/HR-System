<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Annual leave balances
            $table->integer('annual_leave_balance')->default(21)->after('branch_name');
            $table->integer('annual_leave_used')->default(0)->after('annual_leave_balance');
            
            // Short leave balances
            $table->integer('short_leave_balance')->default(36)->after('annual_leave_used');
            $table->integer('short_leave_used')->default(0)->after('short_leave_balance');
            
            // Monthly tracking
            $table->integer('monthly_leaves_used')->default(0)->after('short_leave_used');
            $table->integer('monthly_half_leaves_used')->default(0)->after('monthly_leaves_used');
            $table->integer('monthly_short_leaves_used')->default(0)->after('monthly_half_leaves_used');
            
            // Reset tracking
            $table->string('last_monthly_reset')->nullable()->after('monthly_short_leaves_used');
            $table->date('leave_year_start')->after('last_monthly_reset');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'annual_leave_balance',
                'annual_leave_used',
                'short_leave_balance', 
                'short_leave_used',
                'monthly_leaves_used',
                'monthly_half_leaves_used',
                'monthly_short_leaves_used',
                'last_monthly_reset',
                'leave_year_start'
            ]);
        });
    }
};