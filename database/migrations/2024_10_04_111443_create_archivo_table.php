<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivosTable extends Migration
{
    public function up()
    {
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Nombre del archivo
            $table->text('contenido')->nullable(); // Contenido del archivo (puede ser una ruta a un archivo en el servidor o contenido embebido)
            $table->foreignId('propietario')->constrained('users')->onDelete('cascade'); // Propietario del archivo
            $table->foreignId('carpeta_id')->constrained('carpeta')->onDelete('cascade'); // Relacionado con la tabla carpeta

            // Relación con la tabla archivo_version para la versión actual
            $table->foreignId('version_actual_id')->nullable()->constrained('archivo_version')->onDelete('set null');

            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('archivos');
    }
}
