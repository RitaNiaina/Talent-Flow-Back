<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entretiens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidature_id')->constrained('candidatures')->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade'); // Recruteur
            $table->enum('type_entretien', ['en ligne', 'présentiel']);
            $table->string('lien_meet')->nullable(); // lien meet pour entretien en ligne
            $table->string('lieu')->nullable(); // lieu si présentiel
            $table->dateTime('date_entretien');
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entretiens');
    }
};
