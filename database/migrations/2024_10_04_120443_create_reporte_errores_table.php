<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReporteErroresTable extends Migration
{
    public function up()
    {
        Schema::create('reporte_errores', function (Blueprint $table) {
            $table->id();
            $table->text('descripcion');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reporte_errores');
    }
}
