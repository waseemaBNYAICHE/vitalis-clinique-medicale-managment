<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_ordonnances', function (Blueprint $table) {
            $table->id('id_ligne_ordonnance');

            $table->string('dosologie');
            $table->string('frequence');
            $table->string('duree');
            $table->integer('quantite');
            $table->text('instructions');

            $table->unsignedBigInteger('id_ordonnance');
            $table->unsignedBigInteger('id_medicament');

            $table->foreign('id_ordonnance')
                ->references('id_ordonnance')
                ->on('ordonnances');

            $table->foreign('id_medicament')
                ->references('id_medicament')
                ->on('medicaments');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_ordonnances');
    }
};