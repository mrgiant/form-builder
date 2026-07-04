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
        Schema::create('gl_answers', function (Blueprint $table) {
            $table->id();

            $table->text('value')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('question_id');
            $table->foreign('question_id')->references('id')->on('gl_questions')->onDelete('cascade');

            $table->unsignedBigInteger('form_response_id');
            $table->foreign('form_response_id')->references('id')->on('gl_form_responses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gl_answers');
    }
};
