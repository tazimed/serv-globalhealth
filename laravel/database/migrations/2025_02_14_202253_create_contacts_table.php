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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id('ID_Contact');
            $table->string('Nom');
            $table->string('Prenom');
            $table->date('Birthday');
            $table->string('N_assurance');
            $table->string('Cnss');
            $table->string('Telephone');
            $table->string('Email');
            $table->string('Adresse');
            $table->text('preferences');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
