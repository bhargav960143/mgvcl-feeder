<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feeders', function (Blueprint $table) {
            $table->string('category', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('feeders', function (Blueprint $table) {
            $table->enum('category', ['URBAN', 'GIDC', 'HTEX', 'EHT', 'SST', 'IND'])->change();
        });
    }
};
