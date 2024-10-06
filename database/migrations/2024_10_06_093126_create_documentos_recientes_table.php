<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration for Documentos Recientes
class CreateDocumentosRecientesTable extends Migration
{
    public function up()
    {
        Schema::create('documentos_recientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('archivo_id')->constrained('archivos')->onDelete('cascade');
            $table->timestamp('fecha_acceso');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documentos_recientes');
    }
}

