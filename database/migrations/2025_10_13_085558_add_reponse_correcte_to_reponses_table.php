<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Appliquer la migration.
     */
    public function up(): void
    {
        Schema::table('reponses', function (Blueprint $table) {
            // Ajouter le champ pour savoir si la réponse est correcte
            $table->enum('reponse_correcte', ['Vrai', 'Faux'])
                  ->default('Faux')
                  ->after('contenu_reponse')
                  ->comment('Indique si la réponse est correcte : Vrai ou Faux');
        });
    }

    /**
     * Revenir en arrière.
     */
    public function down(): void
    {
        Schema::table('reponses', function (Blueprint $table) {
            $table->dropColumn('reponse_correcte');
        });
    }
};
