<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_prices_config', function (Blueprint $table) {
            $table->id();
            $table->integer('menu_id');
            $table->integer('price_id');
            $table->integer('level_id');
            $table->integer('prices')->default(1);
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('active')->default(0);
            $table->string('package_name');
            $table->integer('sort')->default(0);
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
        Schema::dropIfExists('prices_config');
    }
}
