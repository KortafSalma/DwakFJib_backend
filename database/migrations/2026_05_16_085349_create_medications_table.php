<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pharmacy_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('dosage')->nullable();

            $table->integer('stock')->default(0);

            $table->decimal('price', 10, 2);

            $table->string('category')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};