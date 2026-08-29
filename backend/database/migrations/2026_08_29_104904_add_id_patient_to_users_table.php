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
       Schema::table('users', function (Blueprint $table) {
        $table->unsignedBigInteger('id_patient')->nullable();

        $table->foreign('id_patient')
            ->references('id_patient')
            ->on('patients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['id_patient']);
        $table->dropColumn('id_patient');
        });
    }
};
