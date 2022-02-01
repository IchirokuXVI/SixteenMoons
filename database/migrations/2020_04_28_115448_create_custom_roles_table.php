<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_roles', function (Blueprint $table) {
            //Weak to courses by identify
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('binded_to');
            //$table->unsignedBigInteger('secondary_id')->default(0);
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('price')->default(0);
            //Color displayed in the name of the role (rgb hex example #11111122)
            //$table->string('color', 8)->default('111111');
            //Target level defines which roles are "better" so that a role with lower target level can't target a role with higher target level
            //Roles with the same target level can be targeted (for example if admin has target level 4 and teacher has target level 4 then a teacher can target an admin and vice versa)
            $table->unsignedTinyInteger('target_level');
            $table->timestamps();

            //$table->unique(['course_id', 'secondary_id']);
            //A course isn't able to have two roles with the same name
            $table->unique(['course_id', 'name']);

            //A course can have multiple custom roles (at least 1 because it will be the automatically created with the course)
            $table->foreign('course_id')
                ->on('courses')
                ->references('id')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('binded_to')
                ->on('bind_roles')
                ->references('id');
        });

        Schema::table('custom_roles', function (Blueprint $table) {
            //Trigger for the auto-incremental secondary_id
//            DB::unprepared('
//            CREATE TRIGGER custom_roles_id_ai
//            BEFORE INSERT ON custom_roles FOR EACH ROW
//            BEGIN
//                DECLARE max_id bigInt(20) unsigned;
//                IF (NEW.secondary_id = 0) THEN
//                    SELECT IFNULL(MAX(id), 0)+1 INTO max_id FROM custom_roles WHERE course_id = NEW.course_id;
//                    SET NEW.secondary_id = max_id;
//                END IF;
//             END
//            ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_roles');
    }
}
