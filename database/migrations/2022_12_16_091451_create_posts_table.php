<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title_uz');
            $table->string('title_crl');
            $table->string('title_ru');
            $table->text('description_uz');
            $table->text('description_crl');
            $table->text('description_ru');
            $table->text('content_uz');
            $table->text('content_crl');
            $table->text('content_ru');
            $table->integer('category_id')->unsigned();
            $table->integer('likes')->default(0);
            $table->text('thumbnail')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
