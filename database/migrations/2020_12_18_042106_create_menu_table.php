<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_menu', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id');
            $table->tinyInteger('status')->default(1);
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->text('notes')->nullable();
            $table->text('guide')->nullable();
            $table->string('icon')->nullable();
            $table->string('path')->nullable();
            $table->integer('sort')->default(0);
            $table->tinyInteger('hold')->default(1);
            $table->tinyInteger('hot')->default(0);
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
        Schema::dropIfExists('menu');
    }
}
