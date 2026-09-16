<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SCRUM-613 - Empeche l'envoi de plusieurs rappels pour le meme
 * rendez-vous : la commande de rappel marque cette colonne a true une fois
 * la notification envoyee, et ne reconsidere jamais une ligne deja marquee.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rendez_vous', function (Blueprint $table) {
            $table->boolean('rappel_envoye')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('rendez_vous', function (Blueprint $table) {
            $table->dropColumn('rappel_envoye');
        });
    }
};