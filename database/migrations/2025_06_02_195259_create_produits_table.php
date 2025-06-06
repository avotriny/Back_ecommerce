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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->integer('subcat_id')->constrained()->onDelete('cascade');
            $table->string('nom_prod')->unique();
            $table->string('desc_prod');
            $table->string('prix_prod');
            $table->string('prix_promo')->nullable();
            $table->string('poids_prod')->nullable();
            $table->string('origin_prod')->nullable();
            $table->string('couleur')->nullable();
            $table->string('promotion')->nullable();
            $table->string('like')->nullable();
            $table->string('stock_prod')->nullable();;
              $table->string('taille')->nullable();
               $table->string('pointure')->nullable();
            $table->boolean('status')->default(true);
            $table->string('images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
