<?php

namespace Webkul\Goals\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkul\Goals\Contracts\Goals as GoalsContract;
use Webkul\Lead\Models\Pipeline;
use Webkul\User\Models\User;

class Goals extends Model implements GoalsContract
{
    use SoftDeletes;
    protected $date = ["delete_at"];
    protected $fillable = [
        "id",
        "user_id",
        "pipeline_id",
        "start_date",
        "end_date",
        "target_value",
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
