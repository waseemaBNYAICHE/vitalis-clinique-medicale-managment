<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
            Schema::create('ordonnances', function (Blueprint $table) {
            $table->id('id_ordonnance');

            $table->date('date_ordonnance');
            $table->text('instructions_generales');
            $table->string('duree_traitement');
            $table->string('type');

            $table->unsignedBigInteger('id_consultation');

            $table->foreign('id_consultation')
                ->references('id_consultation')
                ->on('consultations');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnances');
    }
};