<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitalisations', function (Blueprint $table) {
            $table->id('id_hospitalisation');

            $table->date('date_entree');
            $table->time('heure_entree');
            $table->date('date_sortie')->nullable();
            $table->text('motif_hospitalisation');
            $table->text('diagnostic_entree');
            $table->string('statut');
            $table->text('observations')->nullable();

            $table->unsignedBigInteger('id_patient');
            $table->unsignedBigInteger('id_chambre');
            $table->unsignedBigInteger('id_medecin');

            $table->foreign('id_patient')
                ->references('id_patient')
                ->on('patients');

            $table->foreign('id_chambre')
                ->references('id_chambre')
                ->on('chambres');

            $table->foreign('id_medecin')
                ->references('id_medecin')
                ->on('medecins');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitalisations');
    }
};