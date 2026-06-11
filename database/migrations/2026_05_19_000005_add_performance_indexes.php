<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('email');
            $table->index('created_at');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->index('status');
            $table->index('user_id');
            $table->index('pharmacy_id');
            $table->index(['status', 'created_at']);
            $table->index('expires_at');
        });

        Schema::table('medications', function (Blueprint $table) {
            $table->index('pharmacy_id');
            $table->index('category');
            $table->index('stock');
            $table->index('price');
            $table->fullText('name');
        });

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->index('city');
            $table->index('is_verified');
            $table->index(['latitude', 'longitude']);
            $table->index('rating');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('pharmacy_id');
            $table->index('distributor_id');
            $table->index(['status', 'created_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['pharmacy_id']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['expires_at']);
        });

        Schema::table('medications', function (Blueprint $table) {
            $table->dropIndex(['pharmacy_id']);
            $table->dropIndex(['category']);
            $table->dropIndex(['stock']);
            $table->dropIndex(['price']);
            $table->dropIndex(['name']);
        });

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropIndex(['city']);
            $table->dropIndex(['is_verified']);
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropIndex(['rating']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['pharmacy_id']);
            $table->dropIndex(['distributor_id']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropIndex(['type']);
        });
    }
};
