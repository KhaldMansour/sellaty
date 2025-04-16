<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryWantedProductTable extends Migration
{
    public function up()
    {
        Schema::create('category_wanted_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wanted_product_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('category_wanted_product');
    }
}