<?php

namespace Webkul\Goals\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Goals\Contracts\Goals as GoalsContract;

class Goals extends Model implements GoalsContract
{
    protected $fillable = [
        "id",
        "goals_id",
        "start_date",
        "end_date",
        "target",
    ];
}
