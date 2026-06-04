<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('substations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_division_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->timestamps();

            $table->unique(['sub_division_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('substations');
    }
};
