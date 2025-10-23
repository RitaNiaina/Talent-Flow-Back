<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reponses_candidats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidat_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('reponse_id')->constrained('reponses')->onDelete('cascade');
            $table->enum('reponse_correcte', ['Vrai', 'Faux']);
            $table->text('contenu_reponse');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reponses_candidats');
    }
};
