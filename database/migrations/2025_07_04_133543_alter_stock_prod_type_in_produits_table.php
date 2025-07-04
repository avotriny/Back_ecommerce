<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) S’assurer d’avoir installé doctrine/dbal pour pouvoir modifier le schéma :
        //    composer require doctrine/dbal

        // 2) Conversion via SQL brut
        DB::statement(<<<SQL
            ALTER TABLE produits
            ALTER COLUMN stock_prod TYPE integer
            USING stock_prod::integer
        SQL);
    }

    public function down(): void
    {
        // Si rollback, remettre en string (avec DEFAULT '0' pour éviter NOT NULL violation)
        DB::statement(<<<SQL
            ALTER TABLE produits
            ALTER COLUMN stock_prod TYPE varchar
            USING stock_prod::varchar
        SQL);
    }
};
