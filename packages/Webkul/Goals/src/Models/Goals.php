<?php

namespace Webkul\Goals\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Goals\Contracts\Goals as GoalsContract;
use Webkul\Lead\Models\Pipeline;
use Webkul\User\Models\User;

class Goals extends Model implements GoalsContract
{
    protected $fillable = [
        "id",
        "goals_id",
        "start_date",
        "end_date",
        "target",
    ];
    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
