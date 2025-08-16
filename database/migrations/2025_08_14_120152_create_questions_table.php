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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('intitule_question');
            $table->string('type_question');
            $table->integer('points_question');
            $table->unsignedBigInteger('test_id');
            $table->foreign('test_id')
                  ->references('id')
                  ->on('tests')
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
        Schema::table('questions', function (Blueprint $table) {
        $table->dropForeign(['test_id']); // Supprimer la contrainte
        });
        Schema::dropIfExists('questions');
    }
};
