<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('employee_id')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('phone', 15)->nullable();
            $table->string('password');
            $table->enum('jurisdiction_type', ['global', 'circle', 'division', 'sub_division'])->default('global');
            $table->unsignedBigInteger('jurisdiction_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
