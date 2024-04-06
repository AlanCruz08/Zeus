<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coches', function (Blueprint $table) {
            // Eliminar la restricción de clave externa
            $table->dropForeign('coches_user_id_foreign');
            // Eliminar la columna user_id
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coches', function (Blueprint $table) {
            // Agregar la columna user_id
            $table->unsignedBigInteger('user_id');
            // Restaurar la restricción de clave externa
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
