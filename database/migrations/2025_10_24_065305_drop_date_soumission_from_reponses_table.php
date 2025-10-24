<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reponses', function (Blueprint $table) {
            if (Schema::hasColumn('reponses', 'date_soumission')) {
                $table->dropColumn('date_soumission');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reponses', function (Blueprint $table) {
            if (!Schema::hasColumn('reponses', 'date_soumission')) {
                $table->timestamp('date_soumission')->nullable()->after('contenu_reponse');
            }
        });
    }
};
