<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ⚠️ Pour modifier un ENUM, il faut utiliser une requête SQL brute
        DB::statement("ALTER TABLE candidatures MODIFY etat_candidature ENUM('en_attente', 'en_cours', 'acceptee', 'refusee') DEFAULT 'en_attente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancien ENUM sans "en_cours"
        DB::statement("ALTER TABLE candidatures MODIFY etat_candidature ENUM('en_attente', 'acceptee', 'refusee') DEFAULT 'en_attente'");
    }
};
