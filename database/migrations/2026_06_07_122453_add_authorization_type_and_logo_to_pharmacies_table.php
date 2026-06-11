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
        Schema::table('pharmacies', function (Blueprint $table) {
            if (!Schema::hasColumn('pharmacies', 'logo')) {
                $table->string('logo')->nullable()->after('description');
            }
            if (!Schema::hasColumn('pharmacies', 'authorization_type')) {
                $table->string('authorization_type')->nullable()->after('total_reviews');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            if (Schema::hasColumn('pharmacies', 'logo')) {
                $table->dropColumn('logo');
            }
            if (Schema::hasColumn('pharmacies', 'authorization_type')) {
                $table->dropColumn('authorization_type');
            }
        });
    }
};
