<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Criteria\SearchByCategoryCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class IntroMessageRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    protected $fieldSearchable = [
        'name' => 'like',
        'featured',
    ];

    public function model()
    {
        return Product::class;
    }

    public function boot()
    {
        $this->pushCriteria(new SearchByCategoryCriteria());

        $this->pushCriteria(app(RequestCriteria::class));
    }
}
