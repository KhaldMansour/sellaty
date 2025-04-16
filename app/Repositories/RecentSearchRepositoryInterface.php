<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Models\RecentSearch;
use Prettus\Repository\Contracts\RepositoryInterface;

interface RecentSearchRepositoryInterface extends RepositoryInterface
{
    public function save(int $userId, string $field, string $value, Model $searchable): RecentSearch;
}
