<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
            Schema::create('medicaments', function (Blueprint $table) {
            $table->id('id_medicament');

            $table->string('nom_medicament');
            $table->string('forme');
            $table->string('dosage');
            $table->string('fabricant');
            $table->text('description');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicaments');
    }
};