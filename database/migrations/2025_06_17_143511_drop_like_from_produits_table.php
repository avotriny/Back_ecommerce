<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('produits', function (Blueprint $table) {
            // Supprime la colonne 'like'
            if (Schema::hasColumn('produits', 'like')) {
                $table->dropColumn('like');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produits', function (Blueprint $table) {
            // Recréé la colonne 'like' si on fait un rollback
            $table->boolean('like')->default(false)->after('status');
        });
    }
};
