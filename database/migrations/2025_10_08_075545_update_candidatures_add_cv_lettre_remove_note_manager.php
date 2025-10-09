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
        Schema::table('candidatures', function (Blueprint $table) {
            // Ajouter les colonnes seulement si elles n'existent pas
            if (!Schema::hasColumn('candidatures', 'cv_candidat')) {
                $table->string('cv_candidat')->nullable()->after('etat_candidature');
            }

            if (!Schema::hasColumn('candidatures', 'lettre_motivation')) {
                $table->string('lettre_motivation')->nullable()->after('cv_candidat');
            }

            // Supprimer la clé étrangère et la colonne manager_id si elle existe
            if (Schema::hasColumn('candidatures', 'manager_id')) {
                $table->dropForeign(['manager_id']);
                $table->dropColumn('manager_id');
            }

            // Supprimer la colonne note_candidature si elle existe
            if (Schema::hasColumn('candidatures', 'note_candidature')) {
                $table->dropColumn('note_candidature');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Supprimer les colonnes ajoutées si elles existent
            if (Schema::hasColumn('candidatures', 'cv_candidat')) {
                $table->dropColumn('cv_candidat');
            }

            if (Schema::hasColumn('candidatures', 'lettre_motivation')) {
                $table->dropColumn('lettre_motivation');
            }

            // Ajouter à nouveau les colonnes supprimées
            if (!Schema::hasColumn('candidatures', 'note_candidature')) {
                $table->integer('note_candidature')->nullable();
            }

            if (!Schema::hasColumn('candidatures', 'manager_id')) {
                $table->unsignedBigInteger('manager_id')->nullable();
                $table->foreign('manager_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }
};
