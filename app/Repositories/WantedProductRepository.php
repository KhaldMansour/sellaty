<?php

namespace App\Repositories;

use App\Models\WantedProduct;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class IntroMessageRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class WantedProductRepository extends BaseRepository implements WantedProductRepositoryInterface
{
    protected $fieldSearchable = [
        'name' => 'like'
    ];

    public function model()
    {
        return WantedProduct::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
