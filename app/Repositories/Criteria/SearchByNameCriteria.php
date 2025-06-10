<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SearchByNameCriteria implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        $search = request('search');

        if (empty($search)) {
            return $model;
        }

        $conditions = explode(';', $search);
        $name = null;

        foreach ($conditions as $condition) {
            if (strpos($condition, ':') !== false) {
                list($key, $value) = explode(':', $condition, 2);
                if ($key === 'name') {
                    if (trim($value) === '') {
                        return $model->whereRaw('0 = 1');
                    }
                    $name = $value;
                    break;
                }
            }
        }

        if (!empty($name)) {
            $locales = config('app.supported_locales');

            $model = $model->where(function ($query) use ($locales, $name) {
                foreach ($locales as $index => $locale) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}('name_'.$locale, 'like', $name.'%');
                }
            });
        }

        return $model;
    }
}
