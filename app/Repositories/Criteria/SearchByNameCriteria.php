<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SearchByNameCriteria implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        $locale = app()->getLocale();

        if (request()->has('search') && strpos(request('search'), 'name') !== false) {
            $search = request('search'); 
            $pairs = explode(';', $search);

            foreach ($pairs as $pair) {
                list($key, $value) = explode(':', $pair);

                if ($key === 'name') {
                    $model = $model->where('name->'. $locale, 'like' ,$value);
                }
            }
        }

        return $model;
    }
}
