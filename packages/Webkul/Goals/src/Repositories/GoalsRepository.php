<?php

namespace Webkul\Goals\Repositories;

use Webkul\Core\Eloquent\Repository;

class GoalsRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Goals\Contracts\Goals';
    }
}
