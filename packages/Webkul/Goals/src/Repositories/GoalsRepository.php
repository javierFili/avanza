<?php

namespace Webkul\Goals\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Goals\Models\Goals;

class GoalsRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'Webkul\Goals\Contracts\Goals';
    }

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function create(array $data)
    {
        try {
            $goal = new Goals;
            $goal->user_id = $data['user_id'];
            $goal->pipeline_id = $data['pipeline_id'];
            $goal->start_date = $data['date_start'];
            $goal->end_date = $data['date_end'];
            $goal->target_value = $data['target_value'];
            $goal->save();

            return $goal;
        } catch (\Exception $e) {
            return false;
        }

        return $goal;
    }

    /**
     * Update a new repository instance.
     *
     * @return void
     */
    public function update($data, $id)
    {
        try {
            $goal = Goals::find($id);
            // dd($goal);
            $goal->update($data);

            return [$goal, true];
        } catch (\Exception $e) {
            return [$e, false];
        }
    }

    /**
     * Delete a new repository instance.
     *
     * @return void
     */
    public function delete($id)
    {
        try {
            $goal = Goals::find($id);
            $goal->delete();

            return [$goal, true];
        } catch (\Exception $e) {
            return [$e, false];
        }
    }

    /**
     * Get all goals
     *
     * @return void
     */
    public function getAllGoals()
    {
        return $this->model->with('user')->with('pipeline')->paginate(12);
    }

    /**
     * Get all goals by user
     *
     * @return void
     */
    public function getAllGoalsByUser($userId)
    {
        return $this->model->where('user_id', $userId)->with('user')->get();
    }

    /**
     * Get all goals by user
     *
     * @return void
     */
    public function getAllGoalsByPipeline($pipelineId)
    {
        return $this->model->where('pipeline_id', $pipelineId)->with('user')->get();
    }

    /**
     * Get all goals by user
     *
     * @return void
     */
    public function getAllGoalsByPipelineStage($pipelineStageId)
    {
        return $this->model->where('pipeline_stage_id', $pipelineStageId)->with('user')->get();
    }

    /**
     * Get gols target_value for user,pipeline, dates
     */
    public function userStatitics($data)
    {
        $userExits = $this->model
            ->where('user_id', $data['user_id'])
            ->where('pipeline_id', $data['pipeline_id']);
        if (! $userExits) {
            return false;
        }

        $valueGoal = $this->model
            ->where('user_id', $data['user_id'])
            ->where('pipeline_id', $data['pipeline_id'])
            ->where(function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->where('end_date', '>=', $data['start_date'])
                        ->where('start_date', '<=', $data['end_date']);
                });
            })
            ->get();
            //->first()?->target_value;

        return $valueGoal;
    }

    public function getByUserIdPipelineIdAndDate($data)
    {
        $goal = $this->model
            ->where('pipeline_id', $data['pipeline_id'])
            ->where('user_id', $data['user_id'])
            ->where('start_date', '>=', $data['date_start'])
            ->where('end_date', '<=', $data['date_end'])
            ->get();

        return $goal;
    }

    /**
     * get all goals by user_id, pipeline_id, start_date, end_date
     */
    public function existsGoalInDateRange($data)
    {
        return $this->model
            ->where('pipeline_id', $data['pipeline_id'])
            ->where('user_id', $data['user_id'])
            ->where(function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->where('end_date', '>=', $data['date_start'])
                        ->where('start_date', '<=', $data['date_end']);
                });
            })
            ->first();
    }
}
