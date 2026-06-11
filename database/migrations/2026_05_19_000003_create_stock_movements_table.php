<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained('medications')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->string('type'); // IN, OUT, ADJUSTMENT, EXPIRED
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['medication_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
