<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivoVersionTable extends Migration
{
    public function up()
    {
        // Verificar si la tabla ya existe antes de crearla
        if (!Schema::hasTable('archivo_version')) {
            Schema::create('archivo_version', function (Blueprint $table) {
                $table->id();
                $table->foreignId('archivo_id')->constrained('archivos')->onDelete('cascade');
                $table->integer('version');
                $table->timestamp('fecha');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('archivo_version');
    }
}
