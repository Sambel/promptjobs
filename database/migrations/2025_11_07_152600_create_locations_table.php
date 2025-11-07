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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "France", "Europe", "Worldwide"
            $table->enum('type', ['country', 'region', 'worldwide', 'timezone'])->default('country');
            $table->string('region_parent')->nullable(); // e.g., "Europe" for "France"
            $table->boolean('timezone_based')->default(false); // true for timezone locations
            $table->timestamps();

            $table->index('type');
            $table->index('region_parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
