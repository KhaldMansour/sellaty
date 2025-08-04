<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Chat;
use App\Models\ChatMessage;
use Carbon\Carbon;

class ChatStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        $totalToday = Chat::whereDate('created_at', $today)->count();
        $totalWeek = Chat::where('created_at', '>=', $thisWeek)->count();
        $totalMonth = Chat::where('created_at', '>=', $thisMonth)->count();

        $totalMessages = ChatMessage::count();
        $totalChats = Chat::count();
        $avgMessages = $totalChats ? round($totalMessages / $totalChats, 1) : 0;

        return [
            Stat::make('Chats Today', $totalToday),
            Stat::make('Chats This Week', $totalWeek),
            Stat::make('Chats This Month', $totalMonth),
            Stat::make('Avg Messages / Chat', $avgMessages),
        ];
    }
}
