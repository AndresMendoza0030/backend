<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration for Documentos Favoritos
class CreateDocumentosFavoritosTable extends Migration
{
    public function up()
    {
        Schema::create('documentos_favoritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('archivo_id')->constrained('archivos')->onDelete('cascade');
            $table->timestamp('fecha_agregado')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documentos_favoritos');
    }
}
