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
        Schema::create('gl_questions', function (Blueprint $table) {
            $table->id();
            $table->string('question_type')->default('text');
            $table->string('label');
            $table->longText('label_free_text')->nullable();
            $table->text('description')->nullable();
            $table->text('options')->nullable();
            $table->boolean('required')->default(0);
            $table->boolean('unique')->default(0);
            $table->string('css_class')->nullable();
            $table->longText('css_styles')->nullable();
            $table->string('question_size_col')->default('col-md-12');
            $table->integer('order')->nullable();

            // File upload settings
            $table->string('maximum_file_size')->nullable();
            $table->string('allow_only_specific_file_types')->nullable();

            // Pattern validation
            $table->string('pattern_validation')->nullable();
            $table->text('pattern_validation_message')->nullable();

            $table->unsignedBigInteger('form_id');
            $table->foreign('form_id')->references('id')->on('gl_forms')->onDelete('cascade');

            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('gl_questions')->onDelete('cascade');

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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('gl_questions');
        Schema::enableForeignKeyConstraints();
    }
};
