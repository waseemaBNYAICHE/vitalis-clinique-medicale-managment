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
      Schema::create('medecins', function (Blueprint $table) {
        $table->id('id_medecin');

        $table->string('matricule')->unique();
        $table->string('nom');
        $table->string('prenom');
        $table->string('telephone');
        $table->string('email')->unique();
        $table->date('date_embauche');
        $table->decimal('tarif_consultation', 10, 2);

        $table->unsignedBigInteger('id_specialite');

        $table->foreign('id_specialite')
        ->references('id_specialite')
        ->on('specialites');

        $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medecins');
    }
};
