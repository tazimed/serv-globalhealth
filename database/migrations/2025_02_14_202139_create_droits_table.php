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
        Schema::create('droits', function (Blueprint $table) {
            $table->id('ID_Droit');
            $table->string('Droit');
            $table->boolean('Lecture');
            $table->boolean('Ajouter');
            $table->boolean('Modifier');
            $table->boolean('Supprimer');
            $table->foreignId('ID_Role')->constrained('roles', 'ID_Role')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('droits');
    }
};
