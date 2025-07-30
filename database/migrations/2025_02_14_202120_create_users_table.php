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
        Schema::create('users', function (Blueprint $table) {
            $table->id('ID_User');
            $table->string('Nom');
            $table->string('Prenom');
            $table->string('Email');
            $table->string('Password');
            $table->string('Photo');
            $table->string('Post');
            $table->string('Tel');
            $table->string('Sex'); 
            $table->string('Adresse');
            $table->string('Specialisation');
            $table->decimal('Salaire', 8, 2);
            $table->decimal('Heur_sup_prime', 8, 2);
            $table->integer('Delai_rappel');
            $table->foreignId('ID_Role')->constrained('roles', 'ID_Role')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
