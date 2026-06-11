<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyal_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('total_visits')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0);
            $table->integer('loyalty_points')->default(0);
            $table->string('tier')->default('Bronze');
            $table->timestamp('last_purchase_at')->nullable();
            $table->timestamps();

            $table->unique(['pharmacy_id', 'user_id']);
            $table->index(['pharmacy_id', 'tier']);
            $table->index(['pharmacy_id', 'total_spent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyal_patients');
    }
};
