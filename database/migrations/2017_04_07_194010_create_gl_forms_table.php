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
        Schema::create('gl_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('thank_you_message')->nullable();
            $table->text('close_message')->nullable();
            $table->text('not_start_message')->nullable();
            $table->string('slug')->unique()->index();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('show_number')->default(1);
            $table->dateTime('begin_at')->nullable();
            $table->dateTime('end_at')->nullable();

            // Email notifications
            $table->tinyInteger('email_notifications_new_responses')->default(0);
            $table->tinyInteger('attachment_in_email_notifications')->default(0);
            $table->json('emails')->nullable();

            // Custom design
            $table->longText('custom_html')->nullable();
            $table->longText('custom_css')->nullable();
            $table->longText('custom_head')->nullable();
            $table->longText('custom_js')->nullable();

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
        Schema::dropIfExists('gl_forms');
    }
};
