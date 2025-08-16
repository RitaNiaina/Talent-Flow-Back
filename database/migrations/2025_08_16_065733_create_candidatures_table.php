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
        Schema::create('candidatures', function (Blueprint $table) {
            $table->id();
            $table->date('date_postule');
            $table->enum('etat_candidature', ['en_attente', 'acceptee', 'refusee'])->default('en_attente');
            $table->integer('note_candidature')->nullable();
            $table->unsignedBigInteger('candidat_id');
            $table->unsignedBigInteger('manager_id');
            $table->unsignedBigInteger('offre_id');

            $table->foreign('candidat_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('manager_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('offre_id')
                  ->references('id')
                  ->on('offres')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        chema::table('candidatures', function (Blueprint $table) {
            $table->dropForeign(['candidat_id']);
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['offre_id']);
        });
        Schema::dropIfExists('candidatures');
    }
};
