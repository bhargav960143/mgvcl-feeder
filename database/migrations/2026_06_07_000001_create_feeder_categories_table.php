<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeder_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->timestamps();
        });

        // Seed existing hardcoded categories
        DB::table('feeder_categories')->insert(array_map(
            fn($name) => ['name' => $name, 'created_at' => now(), 'updated_at' => now()],
            ['URBAN', 'GIDC', 'HTEX', 'EHT', 'SST', 'IND']
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('feeder_categories');
    }
};
