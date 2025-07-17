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
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id('ID_Rendez_Vous');
            $table->string('Frequence');
            $table->date('Date');
            $table->string('Status');
            $table->foreignId('ID_User')->constrained('users','ID_User')->onDelete('cascade');
            $table->foreignId('ID_Contact')->constrained('contacts','ID_Contact')->onDelete('cascade');
            $table->foreignId('ID_Prestation')->constrained('prestations','ID_Prestation')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rendez_vous');
    }
};
