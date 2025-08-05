<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SearchByConditionCriteria implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        $condition = request('condition');

        if (empty($condition)) {
            return $model;
        }

        if (! empty($condition) && $condition) {
            $model = $model->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(`condition`, '$')) ) LIKE ?", ['%"' . strtolower($condition) . '"%']);
        }

        return $model;
    }
}
