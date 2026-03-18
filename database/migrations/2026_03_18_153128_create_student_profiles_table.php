<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->restrictOnDelete();
            $table->string('student_number')->unique();
            $table->string('name');
            $table->string('email');
            $table->string('guardian')->nullable();
            $table->string('contact')->nullable();
            $table->string('address')->nullable();
            $table->string('focus')->nullable();
            $table->string('status')->default('Regular');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};