<?php

namespace App\Repositories;

use App\Models\Chat;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class ChatRepositoryEloquent.
 *
 * @package namespace App\Repositories\App\Models;
 */
class ChatRepository extends BaseRepository implements ChatRepositoryInterface
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Chat::class;
    }



    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
