<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('description_tmp')->nullable()->after('description');
        });

        DB::statement("
            UPDATE products
            SET description_tmp = COALESCE(
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(description, '$.ar')), ''),
                JSON_UNQUOTE(JSON_EXTRACT(description, '$.en'))
            )
        ");

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('description_tmp', 'description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('description');

            $table->json('description')->nullable();
        });
    }
};
