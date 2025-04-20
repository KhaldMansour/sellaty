<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Criteria\SearchByNameCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class IntroMessageRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function model()
    {
        return Category::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(new SearchByNameCriteria());

        $this->pushCriteria(app(RequestCriteria::class));
    }
}
