<?php

namespace App\Repositories;

use App\Models\RecentSearch;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Eloquent\BaseRepository;

class RecentSearchRepository extends BaseRepository implements RecentSearchRepositoryInterface
{
    public function model()
    {
        return RecentSearch::class;
    }

    public function save(int $userId, string $field, string $value, Model $searchable): RecentSearch
    {
        $existing = RecentSearch::where('user_id', $userId)
            ->where('field', $field)
            ->where('value', $value)
            ->where('model', $searchable::class)
            ->first();

        if ($existing) {
            $existing->touch();

            return $existing;
        }

        $recentSearches = RecentSearch::where('user_id', $userId)
            ->where('model', $searchable::class)
            ->orderBy('updated_at')
            ->get();

        if ($recentSearches->count() >= 5) {
            $recentSearches->first()->delete();
        }

        return RecentSearch::create([
            'user_id' => $userId,
            'field' => $field,
            'value' => $value,
            'model' => $searchable::class,
        ]);
    }

    public function getRecentSearchesFor(int $userId, string $modelClass, int $limit = 5)
    {
        return RecentSearch::where('user_id', $userId)
            ->where('model', $modelClass)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
