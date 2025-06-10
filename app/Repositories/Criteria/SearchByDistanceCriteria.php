<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SearchByDistanceCriteria implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        $currentLat = floatval(request('latitude'));
        $currentLng = floatval(request('longitude'));
        $radius = floatval(request('radius'));

        if (empty($currentLat) || empty($currentLng) || empty($radius)) {
            return $model;
        }

        $model->select('*')
        ->selectRaw("(
            6371 * acos(
                cos(radians(?)) *
                cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(latitude))
            )
        ) AS distance", [$currentLat, $currentLng, $currentLat])
        ->whereRaw("(
            6371 * acos(
                cos(radians(?)) *
                cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(latitude))
            )
        ) <= ?", [$currentLat, $currentLng, $currentLat, $radius])
        ->orderBy('distance');

        return $model;
    }
}
