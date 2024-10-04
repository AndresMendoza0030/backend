<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioxUnidadxCargoxSedeTable extends Migration
{
    public function up()
    {
        Schema::create('usuarioxunidadxcargoxsede', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('unidad_id')->constrained('unidad')->onDelete('cascade');
            $table->foreignId('sede_id')->constrained('sede')->onDelete('cascade');
            $table->foreignId('cargo_id')->constrained('cargo')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarioxunidadxcargoxsede');
    }
}
