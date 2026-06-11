<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            if (! Schema::hasColumn('medications', 'generic_name')) {
                $table->string('generic_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('medications', 'requires_prescription')) {
                $table->boolean('requires_prescription')->default(false)->after('stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->dropColumn(['generic_name', 'requires_prescription']);
        });
    }
};
