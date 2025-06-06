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
        Schema::create('commande_tables', function (Blueprint $table) {
            $table->id();
             $table->string('prod_id')->constrained()->onDelete('cascade');
            $table->string('prix_total');
            $table->string('quantite');
            $table->string('nom');
            $table->string('email');
            $table->string('phone');
            $table->string('adresse');
            $table->enum('status', ['en attente', 'livré', 'annulé'])->default('en attente');
            $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_tables');
    }
};
