<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivoXUsuarioXPermisoTable extends Migration
{
    public function up()
    {
        Schema::create('archivoxusuarioxpermiso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archivo_id')->constrained('archivos')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('permiso_id')->constrained('permissions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('archivoxusuarioxpermiso');
    }
}
