<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            // 2) Supprimer ensuite la colonne
            $table->dropColumn('prod_id');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            // Remettre la colonne en string
            $table->string('prod_id')->nullable();
            // Réappliquer la contrainte FK
            $table->foreign('prod_id')
                  ->references('id')
                  ->on('produits')
                  ->onDelete('cascade');
        });
    }
};
