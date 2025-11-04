<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('age')->nullable();
            $table->string('nic')->nullable();
            $table->string('gender')->nullable();
            $table->string('title')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('image')->nullable();
            $table->string('current')->default("Active");
            $table->json('legal_documents')->nullable();
            $table->string('employee_id')->nullable();
            $table->string('description')->nullable();
            $table->date('probation_start_date')->nullable();
            $table->string('probation_period')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->unsignedBigInteger('education_id')->nullable();
            $table->date('employment_start_date')->nullable();
            $table->date('employment_end_date')->nullable();
            $table->string('status')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_no')->nullable();
            $table->string('branch_name')->nullable();
            $table->unsignedBigInteger('loan_monthly_instalment')->nullable();
            $table->timestamps();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('education_id')->references('id')->on('education')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};