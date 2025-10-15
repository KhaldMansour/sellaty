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
        Schema::table('wanted_product_images', function (Blueprint $table) {
            $table->boolean('scanned')->default(false)->after('image_url');
            $table->boolean('is_nsfw')->default(false)->after('scanned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wanted_product_images', function (Blueprint $table) {
            $table->dropColumn(['scanned', 'is_nsfw']);
        });
    }
};
