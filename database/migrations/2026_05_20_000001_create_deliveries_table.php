<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('distributor_id')->constrained()->cascadeOnDelete();
            $table->string('tracking_number')->unique()->nullable();
            $table->string('status')->default('PENDING');
            $table->string('carrier')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            $table->json('tracking_history')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'status']);
            $table->index(['distributor_id', 'status']);
            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
