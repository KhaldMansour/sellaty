<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SearchByPriceCriteria implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        if (request()->has('min_price')) {
            $model = $model->where('price', '>=', request('min_price'));
        }

        if (request()->has('max_price')) {
            $model = $model->where('price', '<=', request('max_price'));
        }

        if (request()->input('price_order') === 'asc') {
            $model = $model->orderBy('price', 'asc') ;
        } else {
            $model = $model->orderBy('price', 'desc') ;
        }

        return $model;
    }
}
