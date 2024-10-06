<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Migration for Bulletin Board
class CreateBulletinBoardTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('bulletin_board')) {
            Schema::create('bulletin_board', function (Blueprint $table) {
                $table->id();
                $table->string('titulo');
                $table->string('imagen');
                $table->timestamp('fecha_publicacion');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('bulletin_board');
    }
}