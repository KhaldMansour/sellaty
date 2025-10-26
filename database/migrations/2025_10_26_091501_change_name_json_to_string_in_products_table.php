<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_tmp')->nullable()->after('id');
        });

        DB::statement("
            UPDATE products
            SET name_tmp = COALESCE(
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar')), ''),
                JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))
            )
        ");

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar', 'name']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('name_tmp', 'name');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropColumn('name');

            $table->json('name');
            $table->string('name_en')
                ->storedAs('JSON_UNQUOTE(name->\"$.en\")')
                ->collation('utf8mb4_0900_ai_ci');
            $table->string('name_ar')
                ->storedAs('JSON_UNQUOTE(name->\"$.ar\")')
                ->collation('utf8mb4_0900_ai_ci');
        });
    }
};
