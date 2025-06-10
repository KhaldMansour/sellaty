<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SearchByCategoryCriteria implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        if (request()->has('category')) {
            $searchValue = request('category');

            $parts = explode(',', $searchValue);
            $categoryId = $parts[0];

            return $model->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', '=', $categoryId);
            });
        }

        return $model;
    }
}
