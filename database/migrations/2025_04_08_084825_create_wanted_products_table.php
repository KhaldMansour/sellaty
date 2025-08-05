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
        Schema::create('wanted_products', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('duration');
            $table->string('currency');
            $table->decimal('min_price', 10, 2);
            $table->decimal('max_price', 10, 2);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('condition');
            $table->json('delivery_options');
            $table->string('address');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('postal_code');
            $table->date('listed_until');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wanted_products');
    }
};
