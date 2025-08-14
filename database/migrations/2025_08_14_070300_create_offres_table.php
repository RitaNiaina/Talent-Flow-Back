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
        Schema::create('offres', function (Blueprint $table) {
            $table->id();
            $table->string('titre_offre');
            $table->text('description_offre');
            $table->date('date_publication');
            $table->date('date_expiration')->nullable();
            $table->enum('statut_offre', ['ouvert', 'fermé', 'en_attente'])->default('ouvert');
            
            // Déclaration explicite de la clé étrangère
            $table->unsignedBigInteger('recruteur_id');
            $table->foreign('recruteur_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('offres', function (Blueprint $table) {
            $table->dropForeign(['recruteur_id']); // Supprimer la contrainte
        });

        Schema::dropIfExists('offres');
    }
};
