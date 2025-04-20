<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SearchByNameCriteria implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        $locale = app()->getLocale();
        $search = request('search');
        $searchFields = request('searchFields');

        if (!$search || !$searchFields) {
            return $model;
        }

        $fieldOperators = collect(explode(';', $searchFields))
            ->mapWithKeys(function ($pair) {
                if (str_contains($pair, ':')) {
                    [$field, $op] = explode(':', $pair, 2);
                    return [$field => $op];
                }
                return [$pair => 'like'];
            });

        if (!array_key_exists('name', $fieldOperators->toArray())) {
            return $model;
        }

        $operator = $fieldOperators['name'];

        if (str_contains($search, ':')) {
            $searchPairs = collect(explode(';', $search))
                ->mapWithKeys(function ($pair) {
                    [$field, $value] = explode(':', $pair, 2);
                    return [$field => $value];
                });

            if (isset($searchPairs['name'])) {
                $value = $searchPairs['name'];
                $model = $model->where("name->{$locale}", $operator, $operator === 'like' ? "%{$value}%" : $value);
            }
        }

        else {
            $value = $search;
            $model = $model->where("name->{$locale}", $operator, $operator === 'like' ? "%{$value}%" : $value);
        }

        return $model;
    }
}
