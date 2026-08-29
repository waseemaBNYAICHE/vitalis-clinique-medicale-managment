<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id('id_patient');
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('sexe');
            $table->string('cin')->unique();
            $table->string('telephone');
            $table->string('email')->unique();
            $table->string('groupe_sanguin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};