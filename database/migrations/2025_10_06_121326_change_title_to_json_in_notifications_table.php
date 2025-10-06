<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('title', 'title_old');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->json('title')->nullable()->after('id');
        });

        $notifications = DB::table('notifications')->select('id', 'title_old')->get();

        foreach ($notifications as $notification) {
            DB::table('notifications')
                ->where('id', $notification->id)
                ->update([
                    'title' => json_encode([
                        'en' => $notification->title_old,
                        'ar' => $notification->title_old,
                    ]),
                ]);
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('title_old');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('title_old')->nullable();
        });

        $notifications = DB::table('notifications')->select('id', 'title')->get();

        foreach ($notifications as $notification) {
            $data = json_decode($notification->title, true);
            $title = $data['en'] ?? $data['ar'] ?? null;

            DB::table('notifications')
                ->where('id', $notification->id)
                ->update(['title_old' => $title]);
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->renameColumn('title_old', 'title');
        });
    }
};
