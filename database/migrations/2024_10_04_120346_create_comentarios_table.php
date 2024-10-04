<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComentariosTable extends Migration
{
    public function up()
    {
        // Verificar si la tabla ya existe antes de crearla
        if (!Schema::hasTable('comentarios')) {
            Schema::create('comentarios', function (Blueprint $table) {
                $table->id();
                // Explicitar el nombre de la tabla en la clave foránea
                $table->foreignId('archivo_ver_id')
                      ->constrained('archivo_version')
                      ->onDelete('cascade');
                $table->text('comentarios');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('comentarios');
    }
}
