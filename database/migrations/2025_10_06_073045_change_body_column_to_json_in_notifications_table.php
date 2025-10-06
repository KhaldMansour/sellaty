<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('body', 'body_old');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->json('body')->nullable()->after('title');
        });

        DB::table('notifications')->select('id', 'body_old')->chunkById(100, function ($notifications) {
            foreach ($notifications as $notification) {
                $text = $notification->body_old ?? '';

                $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $text);

                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update([
                        'body' => json_encode([
                            $isArabic ? 'ar' : 'en' => $text
                        ]),
                    ]);
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('body_old');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('body_old')->nullable();
        });

        DB::table('notifications')->select('id', 'body')->chunkById(100, function ($notifications) {
            foreach ($notifications as $notification) {
                $body = json_decode($notification->body, true);
                $text = $body['en'] ?? $body['ar'] ?? null;

                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update(['body_old' => $text]);
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('body_old', 'body');
        });
    }
};
