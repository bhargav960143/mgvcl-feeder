<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeder_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feeder_id')->constrained()->cascadeOnDelete();
            $table->enum('old_status', ['fully_on', 'partially_on', 'fully_off'])->nullable();
            $table->enum('new_status', ['fully_on', 'partially_on', 'fully_off']);
            $table->text('remarks')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['feeder_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeder_status_logs');
    }
};
