<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->after('category');
            $table->string('batch_number')->nullable()->after('expiry_date');
            $table->decimal('low_stock_threshold', 8, 2)->default(10)->after('stock');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('prescription_file')->nullable()->after('deposit_amount');
            $table->timestamp('expires_at')->nullable()->after('status');
        });

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->string('city')->nullable()->after('address');
            $table->boolean('is_verified')->default(false)->after('longitude');
            $table->decimal('rating', 3, 2)->default(0)->after('is_verified');
            $table->integer('total_reviews')->default(0)->after('rating');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->string('channel')->default('in_app')->after('type'); // in_app, email, sms
            $table->json('metadata')->nullable()->after('channel');
        });
    }

    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->dropColumn(['expiry_date', 'batch_number', 'low_stock_threshold']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['prescription_file', 'expires_at']);
        });

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropColumn(['city', 'is_verified', 'rating', 'total_reviews']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['channel', 'metadata']);
        });
    }
};
