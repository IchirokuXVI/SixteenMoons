<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomRolePrivilegesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_role_privileges', function (Blueprint $table) {
            //Table from the relation of custom roles and privileges, holding the information of the privileges granted to each role, ONLY the granted ones
            //Timestamp was removed so in the model "public $timestamp = false", this way eloquent won't try to insert/update the timestamps
            $table->unsignedBigInteger('custom_role_id');
            $table->unsignedBigInteger('privilege_id');

            //If a privilege is saved in the table then it is granted to the role, if it isn't in the table then it is not granted. Having a boolean could be another option to know if it is granted or not
            // $table->boolean('granted');

            $table->primary(['custom_role_id', 'privilege_id'], 'custom_role_privileges_primary');
        });

        Schema::table('custom_role_privileges', function (Blueprint $table) {
            $table->foreign('custom_role_id', 'custom_role_privileges_role_id')
                ->on('custom_roles')
                ->references('id')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('privilege_id', 'custom_role_privileges_privilege_id')
                ->on('privileges')
                ->references('id');

//            DB::unprepared('
//            ALTER TABLE `custom_role_privileges`
//            ADD CONSTRAINT `custom_role_privileges_role_id`
//            FOREIGN KEY (`custom_role_course_id`, `custom_role_id`)
//            REFERENCES `custom_roles`(`course_id`, `id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
//            ');

            //if custom_roles table has a composite key then the foreign key must reference two fields
            //These fields MUST be sorted this way: first role_id and then role_course_id otherwise it will throw a SQL syntax error when creating the foreign key
//            $table->foreign(['custom_role_course_id', 'custom_role_id'], 'custom_role_privileges_role_id')
//                ->on('custom_roles')
//                ->references(['course_id', 'id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_role_privileges');
    }
}
