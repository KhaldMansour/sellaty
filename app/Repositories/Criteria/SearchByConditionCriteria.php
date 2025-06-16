<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SearchByConditionCriteria implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        $search = request('search');

        if (empty($search)) {
            return $model;
        }
        $searchFields = explode(';', $search);


        $condition = null;
        foreach ($searchFields as $field) {
            if (strpos($field, ':') !== false) {
                list($key, $value) = explode(':', $field, 2);
                if ($key === 'condition') {
                    $condition = $value;
                    break;
                }
            }
        }

        if (! empty($condition) && $condition) {
            $model = $model->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(`condition`, '$')) ) LIKE ?", ['%"' . strtolower($condition) . '"%']);
        }

        return $model;
    }
}
