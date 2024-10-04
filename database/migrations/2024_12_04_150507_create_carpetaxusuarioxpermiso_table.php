<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarpetaXUsuarioXPermisoTable extends Migration
{
    public function up()
    {
        // Verificar si la tabla ya existe antes de crearla
        if (!Schema::hasTable('carpetaxusuarioxpermiso')) {
            Schema::create('carpetaxusuarioxpermiso', function (Blueprint $table) {
                $table->id();
                // Referencia correcta a la tabla 'users'
                $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
                // Referencia correcta a la tabla 'permissions'
                $table->foreignId('permiso_id')->constrained('permissions')->onDelete('cascade');
                // Referencia correcta a la tabla 'carpetas'
                $table->foreignId('carpeta_id')->constrained('carpetas')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('carpetaxusuarioxpermiso');
    }
}
