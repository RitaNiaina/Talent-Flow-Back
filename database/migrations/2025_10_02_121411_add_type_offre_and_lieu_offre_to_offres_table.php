<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offres', function (Blueprint $table) {
            $table->string('type_offre')->nullable()->after('statut_offre'); 
            $table->string('lieu_offre')->nullable()->after('type_offre');
        });
    }

    public function down(): void
    {
        Schema::table('offres', function (Blueprint $table) {
            $table->dropColumn(['type_offre', 'lieu_offre']);
        });
    }
};
