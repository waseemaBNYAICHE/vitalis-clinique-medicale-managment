<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_examen', function (Blueprint $table) {
            $table->string('type_examen')->nullable()->after('date_demande');
        });
    }

    public function down(): void
    {
        Schema::table('demandes_examen', function (Blueprint $table) {
            $table->dropColumn('type_examen');
        });
    }
};