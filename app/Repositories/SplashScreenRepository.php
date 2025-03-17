<?php

namespace App\Repositories;

use App\Models\SplashScreen;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class SplashScreenRepositoryRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class SplashScreenRepository extends BaseRepository implements SplashScreenRepositoryInterface
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return SplashScreen::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
