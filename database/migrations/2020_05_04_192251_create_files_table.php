<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            //$table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('lesson_id')->nullable();
            //$table->unsignedBigInteger('secondary_id')->default(0);
            $table->string('path');
            $table->string('original_name');
            $table->unsignedBigInteger('supported_format_id');
            $table->timestamps();

            $table->foreign('lesson_id', 'files_lesson_id_foreign')
                ->references('id')
                ->on('lessons')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('supported_format_id', 'files_supported_format')
                ->references('id')
                ->on('supported_formats')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        Schema::table('files', function (Blueprint $table) {
//            //Trigger for the auto-incremental secondary_id
//            DB::unprepared('
//            CREATE TRIGGER files_id_ai
//            BEFORE INSERT ON files FOR EACH ROW
//            BEGIN
//                DECLARE max_id bigInt(20) unsigned;
//                IF (NEW.secondary_id = 0) THEN
//                    SELECT IFNULL(MAX(secondary_id), 0)+1 INTO max_id FROM files WHERE course_id = NEW.course_id AND lesson_id = NEW.lesson_id;
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
        Schema::dropIfExists('files');
    }
}
