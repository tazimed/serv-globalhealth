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
        Schema::create('pointage_user', function (Blueprint $table) {
            $table->foreignId('ID_Pointage')->constrained('pointages','ID_Pointage')->onDelete('cascade');
            $table->foreignId('ID_User')->constrained('users','ID_User')->onDelete('cascade');
            $table->integer('Heur_Travail');
            $table->boolean('Abssance');
            $table->primary(['ID_Pointage', 'ID_User']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pointage_user');
    }
};
