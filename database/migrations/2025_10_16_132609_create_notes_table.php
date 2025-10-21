<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidat_id');
            $table->unsignedBigInteger('test_id');
            $table->float('note_candidat', 8, 2)->default(0);
            $table->timestamps();

            $table->foreign('candidat_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
            $table->unique(['candidat_id', 'test_id']); // pour éviter doublon
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
