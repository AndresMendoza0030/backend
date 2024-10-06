<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration for Documentos Compartidos
class CreateDocumentosCompartidosTable extends Migration
{
    public function up()
    {
        Schema::create('documentos_compartidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archivo_id')->constrained('archivos')->onDelete('cascade');
            $table->foreignId('compartido_por')->constrained('users')->onDelete('cascade');
            $table->foreignId('compartido_con')->constrained('users')->onDelete('cascade');
            $table->timestamp('fecha_compartido');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documentos_compartidos');
    }
}