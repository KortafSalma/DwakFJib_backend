<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->tinyInteger('rating')->comment('1-5 stars');
            $table->text('comment')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['pharmacy_id', 'user_id', 'reservation_id']);
            $table->index(['pharmacy_id', 'rating']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
