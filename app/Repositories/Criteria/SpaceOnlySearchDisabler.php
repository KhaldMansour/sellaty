<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SpaceOnlySearchDisabler implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        if (request()->has('search')) {
            $search = request('search'); 
            $pairs = explode(';', $search);

            foreach ($pairs as $pair) {
                list($key, $value) = explode(':', $pair);

                if ($value === '') {
                    $model->whereRaw('1 = 0');
                }
            }
        }

        return $model;
    }
}
