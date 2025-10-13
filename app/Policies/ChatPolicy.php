<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

class ChatPolicy
{
    public function access(User $user, Chat $chat): bool
    {
        return in_array($user->id, $chat->users->pluck('id')->toArray());
    }
}
