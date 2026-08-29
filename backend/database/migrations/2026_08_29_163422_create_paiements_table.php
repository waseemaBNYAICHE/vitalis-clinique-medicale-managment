<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id('id_paiement');

            $table->date('date_paiement');
            $table->time('heure_paiement');
            $table->decimal('montant_paye', 10, 2);
            $table->string('mode_paiement');
            $table->string('statut');
            $table->text('observations')->nullable();

            $table->unsignedBigInteger('id_facture');

            $table->foreign('id_facture')
                ->references('id_facture')
                ->on('factures');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};