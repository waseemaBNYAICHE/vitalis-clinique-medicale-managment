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
       Schema::table('users', function (Blueprint $table) {
        $table->string('prenom')->nullable();
        $table->string('telephone')->nullable();
        $table->string('adresse')->nullable();
        $table->date('date_naissance')->nullable();
        $table->string('sexe')->nullable();
        $table->string('statut')->nullable();
        $table->string('photo_profil')->nullable();
        $table->timestamp('derniere_connexion')->nullable();

        $table->unsignedBigInteger('id_medecin')->nullable();

        $table->foreign('id_medecin')
            ->references('id_medecin')
            ->on('medecins');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['id_medecin']);

        $table->dropColumn([
            'prenom',
            'telephone',
            'adresse',
            'date_naissance',
            'sexe',
            'statut',
            'photo_profil',
            'derniere_connexion',
            'id_medecin',
        ]);
        });
    }
};
