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
    function model()
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
            $goal = new Goals();
            $goal->user_id = $data['user_id'];
            $goal->pipeline_id = $data['pipeline_id'];
            $goal->start_date = $data['date_start'];
            $goal->end_date = $data['date_end'];
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
    public function update(array $data, $id)
    {
        $data['updated_by'] = auth()->user()->id;
        $data['updated_at'] = now();

        return parent::update($data, $id);
    }
    /**
     * Delete a new repository instance.
     *
     * @return void
     */
    public function delete($id)
    {
        $data['deleted_by'] = auth()->user()->id;
        $data['deleted_at'] = now();

        return parent::delete($id);
    }
    /**
     * Get all goals
     *
     * @return void
     */
    public function getAllGoals()
    {
        return $this->model->with('user')->with("pipeline")->paginate(12);
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
}