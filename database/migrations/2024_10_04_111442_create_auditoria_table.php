<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditoriaTable extends Migration
{
    public function up()
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha_hora'); // Momento en que ocurrió la acción
            $table->string('accion'); // Acción realizada (por ejemplo, 'actualización', 'borrado', etc.)
            $table->text('detalles')->nullable(); // Detalles adicionales de la acción
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade'); // Relacionado con la tabla users
            $table->foreignId('version_id')->constrained('archivo_version')->onDelete('cascade'); // Relacionado con la tabla archivo_version
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditoria');
    }
}
