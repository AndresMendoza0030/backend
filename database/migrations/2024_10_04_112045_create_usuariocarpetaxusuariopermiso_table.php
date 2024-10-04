<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarpetaXUsuarioXPermisoTable extends Migration
{
    public function up()
    {
        Schema::create('carpetaxusuarioxpermiso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('permiso_id')->constrained('permissions')->onDelete('cascade');
            $table->foreignId('carpeta_id')->constrained('carpeta')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('carpetaxusuarioxpermiso');
    }
}
