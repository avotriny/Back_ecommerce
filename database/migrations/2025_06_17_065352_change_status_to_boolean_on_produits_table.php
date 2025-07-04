<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            // Si la colonne existe déjà en string, on la modifie :
            $table->boolean('status')->default(true)->change();
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            // Si tu veux revenir au type string :
            $table->string('status')->nullable()->change();
        });
    }
};
