<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('email_notifications')->default(true);
            $table->boolean('in_app_notifications')->default(true);
            $table->boolean('reservation_notifications')->default(true);
            $table->boolean('order_notifications')->default(true);
            $table->boolean('stock_notifications')->default(true);
            $table->boolean('delivery_notifications')->default(true);
            $table->boolean('system_notifications')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
