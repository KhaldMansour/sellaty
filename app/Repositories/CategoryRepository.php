<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Criteria\SearchByNameCriteria;
use App\Repositories\Criteria\SpaceOnlySearchDisabler;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class IntroMessageRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    protected $fieldSearchable = [
        'status',
        'name' => 'like'
    ];

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

        // $this->pushCriteria(new SpaceOnlySearchDisabler());

        $this->pushCriteria(app(RequestCriteria::class));
    }
}
