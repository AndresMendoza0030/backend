<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnunciosTable extends Migration
{
    public function up()
    {
        Schema::create('anuncios', function (Blueprint $table) {
            $table->id();
            $table->text('texto');
            $table->string('imagen')->nullable(); // Permite guardar una URL o el nombre del archivo
            $table->boolean('esta_activo')->default(true); // Indica si el anuncio está activo
            $table->timestamps(); // Incluye created_at y updated_at automáticamente
        });
    }

    public function down()
    {
        Schema::dropIfExists('anuncios');
    }
}
