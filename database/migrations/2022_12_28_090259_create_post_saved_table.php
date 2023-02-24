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
        Schema::create('post_saved', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->constrained('users','id');
            $table->bigInteger('post_id')->unsigned()->constrained('posts','id');
            $table->tinyInteger('status')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_saved');
    }
};
