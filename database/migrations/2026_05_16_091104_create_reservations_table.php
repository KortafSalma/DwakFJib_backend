<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('pharmacy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('medication_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('quantity')->default(1);

            $table->decimal('deposit_amount', 10, 2)
                ->nullable();

            $table->enum('status', [
                'PENDING',
                'PAID'
            ])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
