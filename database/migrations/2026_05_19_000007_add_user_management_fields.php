<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('role');
            $table->timestamp('banned_at')->nullable()->after('is_active');
            $table->text('ban_reason')->nullable()->after('banned_at');
        });

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->time('opening_time')->nullable()->after('description');
            $table->time('closing_time')->nullable()->after('opening_time');
            $table->json('operating_days')->nullable()->after('closing_time');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'banned_at', 'ban_reason']);
        });

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropColumn(['description', 'opening_time', 'closing_time', 'operating_days']);
        });
    }
};
