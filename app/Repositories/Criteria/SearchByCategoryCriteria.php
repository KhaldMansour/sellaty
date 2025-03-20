<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SearchByCategoryCriteria implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        if (request()->has('find') && strpos(request('find'), 'categories.id') !== false) {
            $searchValue = request('find');
            $parts = explode(':', $searchValue);

            if (count($parts) === 2 && $parts[0] === 'categories.id') {
                $categoryId = $parts[1];

                return $model->whereHas('categories', function ($query) use ($categoryId) {
                    $query->where('categories.id', '=', $categoryId);
                });
            }
        }

        return $model;
    }
}
