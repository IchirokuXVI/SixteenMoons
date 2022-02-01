<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomRolesUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_roles_users', function (Blueprint $table) {
            $table->unsignedBigInteger('custom_role_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('supporter')->default(false);

            $table->primary(['custom_role_id', 'user_id']);

            $table->foreign('custom_role_id')
                ->references('id')
                ->on('custom_roles')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('custom_roles_users');
    }
}
