<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resultats', function (Blueprint $table) {
            $table->text('conclusion')->nullable()->change();
            $table->text('valeurs_mesurees')->nullable()->change();
            $table->string('fichier_resultat')->nullable()->change();
            $table->string('image_resultat')->nullable()->change();
            $table->text('observations')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('resultats', function (Blueprint $table) {
            $table->text('conclusion')->nullable(false)->change();
            $table->text('valeurs_mesurees')->nullable(false)->change();
            $table->string('fichier_resultat')->nullable(false)->change();
            $table->string('image_resultat')->nullable(false)->change();
            $table->text('observations')->nullable(false)->change();
        });
    }
};
