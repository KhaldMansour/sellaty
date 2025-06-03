<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('duration');
            $table->string('currency');
            $table->unsignedInteger('quantity')->default(0);
            $table->json('condition');
            $table->json('delivery_options');
            $table->string('address');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('postal_code');
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->date('listed_until');
            $table->string('status')->default('active');
            $table->boolean('negotiable')->default(true);
            $table->boolean('deliverable')->default(true);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
        // Schema::table('product_images', function (Blueprint $table) {
        //     $table->dropForeign(['product_id']);
        // });
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('products');
    }
};
