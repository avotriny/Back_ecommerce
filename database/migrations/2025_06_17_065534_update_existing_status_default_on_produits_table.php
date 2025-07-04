<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Met toutes les lignes existantes à true (1)
        DB::table('produits')->update(['status' => true]);
    }

    public function down(): void
    {
        // Optionnel : repasser à false si vous restorez la migration
        DB::table('produits')->update(['status' => false]);
    }
};
