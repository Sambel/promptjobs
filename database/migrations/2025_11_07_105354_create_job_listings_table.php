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
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('company');
            $table->string('company_logo')->nullable();
            $table->text('description');
            $table->string('location')->nullable();
            $table->boolean('remote')->default(false);
            $table->string('job_type')->default('full-time'); // full-time, part-time, contract
            $table->string('salary_range')->nullable();
            $table->string('apply_url');
            $table->json('tags')->nullable(); // AI/ML skills: GPT, LLM, PyTorch, etc.
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
