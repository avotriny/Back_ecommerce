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
        Schema::create('users', function (Blueprint $table) {
           $table->engine = 'InnoDB';
        $table->id();
        $table->string('name')->unique();
        $table->string('email')->unique();
        $table->string('password')->nullable();
        $table->string('provider')->nullable();
        $table->string('provider_id')->nullable();
        $table->string('avatar')->nullable();
        $table->enum('role', ['admin', 'user', 'guest'])->default('guest');
        $table->enum('active', ['active', 'desactive'])->default('active');   
        $table->timestamp('email_verified_at')->nullable();
        $table->rememberToken();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
