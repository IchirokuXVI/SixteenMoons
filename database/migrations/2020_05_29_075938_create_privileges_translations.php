<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrivilegesTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('privileges_translations', function (Blueprint $table) {
            $table->unsignedBigInteger('privilege_id')->primary();
            $table->string('description_en');
            $table->string('description_es');
            $table->text('long_description_en')->nullable();
            $table->text('long_description_es')->nullable();

            $table->foreign('privilege_id')
                ->references('id')
                ->on('privileges')
                ->onDelete('cascade')
                ->onUpdate('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_roles_users');
    }
}
