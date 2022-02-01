<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            //$table->unsignedBigInteger('secondary_id')->default(0);
            $table->string('title');
            $table->text('body');
            $table->timestamps();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        Schema::table('lessons', function (Blueprint $table) {
            //Trigger for the auto-incremental secondary_id
//            DB::unprepared('
//            CREATE TRIGGER lessons_id_ai
//            BEFORE INSERT ON lessons FOR EACH ROW
//            BEGIN
//                DECLARE max_id bigInt(20) unsigned;
//                IF (NEW.secondary_id = 0) THEN
//                    SELECT IFNULL(MAX(secondary_id), 0)+1 INTO max_id FROM lessons WHERE course_id = NEW.course_id;
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
        Schema::dropIfExists('lessons');
    }
}
