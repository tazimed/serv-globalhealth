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
        Schema::create('document_contacts', function (Blueprint $table) {
            $table->id('Id_Document_contact');
            $table->string('Nom_Doc');
            $table->text('Doc');
            $table->foreignId('ID_Contact')->constrained('contacts','ID_Contact')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_contacts');
    }
};
