<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id('id_consultation');

            $table->string('motif');
            $table->text('diagnostic');
            $table->text('observations')->nullable();
            $table->decimal('poids', 5, 2)->nullable();
            $table->decimal('taille', 5, 2)->nullable();
            $table->string('tension_arterielle')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();

            $table->unsignedBigInteger('id_rendez_vous');

            $table->foreign('id_rendez_vous')
                ->references('id_rendez_vous')
                ->on('rendez_vous');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};