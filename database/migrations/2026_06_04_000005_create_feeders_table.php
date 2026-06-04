<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('substation_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('tnd_code', 20)->unique();
            $table->enum('category', ['URBAN', 'GIDC', 'HTEX', 'EHT', 'SST', 'IND']);
            $table->unsignedInteger('total_consumer')->default(0);
            $table->unsignedInteger('total_tc')->default(0);
            $table->enum('current_status', ['fully_on', 'partially_on', 'fully_off'])->default('fully_on');
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->index('current_status');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeders');
    }
};
