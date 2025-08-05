<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Criteria\SearchByCategoryCriteria;
use App\Repositories\Criteria\SearchByConditionCriteria;
use App\Repositories\Criteria\SearchByDistanceCriteria;
use App\Repositories\Criteria\SearchByNameCriteria;
use App\Repositories\Criteria\SearchByPriceCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class IntroMessageRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    protected $searchJoin = 'and';

    protected $fieldSearchable = [
        'status'
    ];

    public function model()
    {
        return Product::class;
    }

    public function boot()
    {
        $request = request();
        $request->merge(['searchJoin' => 'and']);

        $this->pushCriteria(new SearchByCategoryCriteria());

        $this->pushCriteria(new SearchByNameCriteria());

        $this->pushCriteria(new SearchByDistanceCriteria());

        $this->pushCriteria(new SearchByConditionCriteria());

        $this->pushCriteria(new SearchByPriceCriteria());

        $this->pushCriteria(app(RequestCriteria::class));
    }
}
