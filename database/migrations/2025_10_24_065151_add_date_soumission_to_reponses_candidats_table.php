<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reponses_candidats', function (Blueprint $table) {
            if (!Schema::hasColumn('reponses_candidats', 'date_soumission')) {
                $table->timestamp('date_soumission')->nullable()->after('contenu_reponse');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reponses_candidats', function (Blueprint $table) {
            if (Schema::hasColumn('reponses_candidats', 'date_soumission')) {
                $table->dropColumn('date_soumission');
            }
        });
    }
};
