<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_details', function (Blueprint $table) {
            $table->string('bank_code')->nullable()->change();
            $table->string('branch_code')->nullable()->change();
            $table->string('branch')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bank_details', function (Blueprint $table) {
            $table->string('bank_code')->nullable(false)->change();
            $table->string('branch_code')->nullable(false)->change();
            $table->string('branch')->nullable(false)->change();
        });
    }
};
