<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_examen', function (Blueprint $table) {
            $table->id('id_demande_examen');

            $table->date('date_demande');
            $table->string('niveau_urgence');
            $table->text('indications_cliniques');
            $table->string('statut');
            $table->date('date_prevue');
            $table->date('date_realisation');
            $table->text('observation');

            $table->unsignedBigInteger('id_consultation');

            $table->foreign('id_consultation')
                ->references('id_consultation')
                ->on('consultations');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_examen');
    }
};