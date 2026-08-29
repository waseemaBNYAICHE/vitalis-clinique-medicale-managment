<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chambres', function (Blueprint $table) {
            $table->id('id_chambre');

            $table->string('numero_chambre');
            $table->string('type_chambre');
            $table->string('etage');
            $table->integer('capacite');
            $table->decimal('tarif_journalier', 10, 2);
            $table->string('statut');
            $table->text('description');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chambres');
    }
};