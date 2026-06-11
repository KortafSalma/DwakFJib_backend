<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            if (!Schema::hasColumn('medications', 'barcode')) {
                $table->string('barcode')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('medications', 'is_derma')) {
                $table->boolean('is_derma')->default(false)->after('category');
            }
            if (!Schema::hasColumn('medications', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('price');
            }
            if (!Schema::hasColumn('medications', 'photo_front')) {
                $table->string('photo_front')->nullable()->after('description');
            }
            if (!Schema::hasColumn('medications', 'photo_back')) {
                $table->string('photo_back')->nullable()->after('photo_front');
            }
            if (!Schema::hasColumn('medications', 'photo_left')) {
                $table->string('photo_left')->nullable()->after('photo_back');
            }
            if (!Schema::hasColumn('medications', 'photo_right')) {
                $table->string('photo_right')->nullable()->after('photo_left');
            }
            if (!Schema::hasColumn('medications', 'photo_top')) {
                $table->string('photo_top')->nullable()->after('photo_right');
            }
        });

        Schema::table('pharmacies', function (Blueprint $table) {
            if (!Schema::hasColumn('pharmacies', 'logo')) {
                $table->string('logo')->nullable()->after('description');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'photo')) {
                $table->string('photo')->nullable()->after('ban_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->dropColumn(['barcode', 'is_derma', 'discount_percent', 'photo_front', 'photo_back', 'photo_left', 'photo_right', 'photo_top']);
        });
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropColumn(['logo']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['photo']);
        });
    }
};
