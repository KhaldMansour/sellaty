<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->json('name')->nullable();
            $table->json('description')->nullable();
            $table->string('image_url');
            $table->string('name_en')
            ->storedAs('JSON_UNQUOTE(name->"$.en")')
            ->collation('utf8mb4_0900_ai_ci');

            $table->string('name_ar')
              ->storedAs('JSON_UNQUOTE(name->"$.ar")')
              ->collation('utf8mb4_0900_ai_ci');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE categories ADD FULLTEXT fulltext_name(name_en, name_ar)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // DB::statement('ALTER TABLE categories DROP INDEX fulltext_name');

        Schema::dropIfExists('category_product');
        Schema::dropIfExists('categories');
    }
};
