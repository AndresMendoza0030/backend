<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarpetasTable extends Migration
{
    public function up()
    {
        Schema::create('carpeta', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('propietario')->constrained('users')->onDelete('cascade');
            $table->foreignId('sede_id')->constrained('sede')->onDelete('cascade');
            $table->foreignId('unidad_id')->constrained('unidad')->onDelete('cascade');
            $table->foreignId('id_padre')->nullable()->constrained('carpeta')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('carpeta');
    }
}
