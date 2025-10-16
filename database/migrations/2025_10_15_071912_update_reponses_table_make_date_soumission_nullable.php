<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reponses', function (Blueprint $table) {
            // rendre la colonne nullable sans supprimer les données
            $table->timestamp('date_soumission')->nullable()->change();
            $table->foreignId('candidat_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reponses', function (Blueprint $table) {
            // revenir à l'état initial si besoin
            $table->timestamp('date_soumission')->nullable(false)->change();
            $table->foreignId('candidat_id')->nullable(false)->change();
        });
    }
};
