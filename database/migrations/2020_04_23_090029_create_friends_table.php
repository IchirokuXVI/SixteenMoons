<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friends', function (Blueprint $table) {
            //The user that sent the friend request will be the one saved on the first column
            $table->unsignedBigInteger('idSender');
            $table->unsignedBigInteger('idReceiver');
            //If the accepted field value is 0 then the friend request has not been accepted nor rejected
            $table->boolean('accepted')->default('0');
            $table->timestamps();

            $table->primary(['idSender', 'idReceiver']);

            $table->foreign('idSender')
                ->references('id')
                ->on('users');

            $table->foreign('idReceiver')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('friends');
    }
}
