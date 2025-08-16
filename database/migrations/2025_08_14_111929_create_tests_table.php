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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('nom_test');
            $table->text('description_test');
            $table->time('duree_test');
            $table->unsignedBigInteger('offre_id');
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
        Schema::table('tests', function (Blueprint $table) {
            $table->dropForeign(['offre_id']); // Supprimer la contrainte
        });
        Schema::dropIfExists('tests');
    }
};
