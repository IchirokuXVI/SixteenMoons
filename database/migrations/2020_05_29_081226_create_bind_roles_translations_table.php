<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBindRolesTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bind_roles_translations', function (Blueprint $table) {
            $table->unsignedBigInteger('bind_roles_id')->primary();
            $table->string('description_en');
            $table->string('description_es');
            $table->text('long_description_en')->nullable();
            $table->text('long_description_es')->nullable();

            $table->foreign('bind_roles_id')
                ->references('id')
                ->on('bind_roles')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bind_roles_translations');
    }
}
