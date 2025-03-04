<?php

namespace App\Repositories;

use App\Models\IntroMessage;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class IntroMessageRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class IntroMessageRepository extends BaseRepository implements IntroMessageRepositoryInterface
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return IntroMessage::class;
    }



    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
