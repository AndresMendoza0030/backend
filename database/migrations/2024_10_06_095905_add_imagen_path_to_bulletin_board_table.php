<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('bulletin_board', function (Blueprint $table) {
        $table->string('imagen_path')->nullable();
    });
}

public function down()
{
    Schema::table('bulletin_board', function (Blueprint $table) {
        $table->dropColumn('imagen_path');
    });
}


};
