<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id('id_facture');

            $table->string('numero_facture');
            $table->date('date_facture');
            $table->decimal('montant_total', 10, 2);
            $table->decimal('remise', 10, 2);
            $table->decimal('montant_net', 10, 2);
            $table->string('statut_paiement');
            $table->text('observations')->nullable();

            $table->unsignedBigInteger('id_consultation')->nullable();
            $table->unsignedBigInteger('id_hospitalisation')->nullable();

            $table->foreign('id_consultation')
                ->references('id_consultation')
                ->on('consultations');

            $table->foreign('id_hospitalisation')
                ->references('id_hospitalisation')
                ->on('hospitalisations');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};