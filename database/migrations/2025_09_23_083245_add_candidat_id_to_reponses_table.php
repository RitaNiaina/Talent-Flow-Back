<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reponses', function (Blueprint $table) {
            // Ajouter la colonne candidat_id
            $table->unsignedBigInteger('candidat_id')->after('id')->nullable();

            // Optionnel : ajouter la clé étrangère vers users (candidat)
            $table->foreign('candidat_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('reponses', function (Blueprint $table) {
            $table->dropForeign(['candidat_id']); // Supprimer la clé étrangère
            $table->dropColumn('candidat_id');    // Supprimer la colonne
        });
    }
};
