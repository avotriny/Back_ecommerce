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
        Schema::create('livraison_faites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')
                  ->constrained('commandes')
                  ->onDelete('cascade');
            $table->string('images')->nullable();
            $table->text('signature')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livraison_faites');
    }
};
