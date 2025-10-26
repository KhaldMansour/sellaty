<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('wanted_products', function (Blueprint $table) {
            $table->string('name_tmp')->nullable()->after('name');
        });

        DB::statement("
            UPDATE wanted_products
            SET name_tmp = COALESCE(
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar')), ''),
                JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))
            )
        ");

        Schema::table('wanted_products', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_tmp', 'name');
            $table->index('name');
        });


        Schema::table('wanted_products', function (Blueprint $table) {
            $table->text('description_tmp')->nullable()->after('description');
        });

        DB::statement("
            UPDATE wanted_products
            SET description_tmp = COALESCE(
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(description, '$.ar')), ''),
                JSON_UNQUOTE(JSON_EXTRACT(description, '$.en'))
            )
        ");

        Schema::table('wanted_products', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->renameColumn('description_tmp', 'description');
        });
    }

    public function down(): void
    {
        Schema::table('wanted_products', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropColumn(['name', 'description']);

            $table->json('name');
            $table->json('description')->nullable();
        });
    }
};
