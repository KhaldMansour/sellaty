<?php

namespace App\Repositories;

use App\Models\Offer;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class OfferRepositoryEloquent.
 *
 * @package namespace App\Repositories\App\Models;
 */
class OfferRepository extends BaseRepository implements OfferRepositoryInterface
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Offer::class;
    }



    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
