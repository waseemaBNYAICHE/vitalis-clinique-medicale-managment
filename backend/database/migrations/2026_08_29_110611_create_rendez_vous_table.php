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
        Schema::create('rendez_vous', function (Blueprint $table) {
        $table->id('id_rendez_vous');

        $table->date('date_rendez_vous');
        $table->time('heure_debut');
        $table->time('heure_fin');
        $table->string('motif');
        $table->string('statut');

        $table->unsignedBigInteger('id_patient');
        $table->unsignedBigInteger('id_medecin');

        $table->foreign('id_patient')
            ->references('id_patient')
            ->on('patients');

        $table->foreign('id_medecin')
            ->references('id_medecin')
            ->on('medecins');

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rendez_vous');
    }
};
