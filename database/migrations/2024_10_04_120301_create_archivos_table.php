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
            $table->string('nombre');
            $table->text('contenido')->nullable();
            $table->foreignId('propietario')->constrained('users')->onDelete('cascade');
            $table->foreignId('carpeta_id')->constrained('carpetas')->onDelete('cascade');
            $table->foreignId('version_actual_id')->nullable()->constrained('archivo_version')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('archivos');
    }
}
