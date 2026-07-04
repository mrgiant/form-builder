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
        Schema::create('gl_form_responses', function (Blueprint $table) {
            $table->id();

            $table->string('ip', 15)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('form_id');
            $table->foreign('form_id')->references('id')->on('gl_forms')->onDelete('cascade');
            $table->string('approval_status')->default('not_required');

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
        Schema::dropIfExists('gl_form_responses');
    }
};
