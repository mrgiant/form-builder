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
        Schema::table('questions', function (Blueprint $table) {
            // Change to UnsignedInteger

            $table->unsignedBigInteger('parent_id')->nullable()->change();

            // Updating relationships
            // $table->foreign('product_category_id')->references('id')->on('categories')->change();
            $table->foreign('parent_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

     /*
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('payment_id');
            $table->dropColumn('open_file_amount');
        });
    }

    */
};
