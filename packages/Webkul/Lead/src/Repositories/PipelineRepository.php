<?php

namespace Webkul\Lead\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Webkul\Core\Eloquent\Repository;
use Webkul\User\Repositories\UserRepository;

class PipelineRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected StageRepository $stageRepository,
        protected UserRepository $userRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return 'Webkul\Lead\Contracts\Pipeline';
    }

    /**
     * Create pipeline.
     *
     * @return \Webkul\Lead\Contracts\Pipeline
     */
    public function create(array $data)
    {
        if ($data['is_default'] ?? false) {
            $this->model->query()->update(['is_default' => 0]);
        }

        $pipeline = $this->model->create($data);

        foreach ($data['stages'] as $stageData) {
            $this->stageRepository->create(array_merge([
                'lead_pipeline_id' => $pipeline->id,
            ], $stageData));
        }

        return $pipeline;
    }

    /**
     * Update pipeline.
     *
     * @param  int  $id
     * @param  string  $attribute
     * @return \Webkul\Lead\Contracts\Pipeline
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        $pipeline = $this->find($id);

        if ($data['is_default'] ?? false) {
            $this->model->query()->where('id', '<>', $id)->update(['is_default' => 0]);
        }

        $pipeline->update($data);

        $previousStageIds = $pipeline->stages()->pluck('id');

        foreach ($data['stages'] as $stageId => $stageData) {
            if (Str::contains($stageId, 'stage_')) {
                $this->stageRepository->create(array_merge([
                    'lead_pipeline_id' => $pipeline->id,
                ], $stageData));
            } else {
                if (is_numeric($index = $previousStageIds->search($stageId))) {
                    $previousStageIds->forget($index);
                }

                $this->stageRepository->update($stageData, $stageId);
            }
        }

        foreach ($previousStageIds as $stageId) {
            $pipeline->leads()->where('lead_pipeline_stage_id', $stageId)->update([
                'lead_pipeline_stage_id' => $pipeline->stages()->first()->id,
            ]);

            $this->stageRepository->delete($stageId);
        }

        return $pipeline;
    }

    /**
     * Return the default pipeline.
     *
     * @return \Webkul\Lead\Contracts\Pipeline
     */
    public function getDefaultPipeline()
    {
        $user = Auth::user()->id;
        $pipelinesUsers = app('Webkul\User\Repositories\UserRepository')
            ->where('id', $user)
            ->with('leadPipelines')
            ->get();
        $pipeline = $pipelinesUsers->first()->leadPipelines->first();

        if (! $pipeline) {
            $pipeline = $this->first();
        }

        return $pipeline;
    }

    /**
     *get all pipelines by user_id
     */
    public function getAllPipelinesByUserId($userId)
    {
        $user = $this->userRepository->where('id', $userId)->with('leadPipelines')->first();
        $pipelines = $user?->leadPipelines;

        return $pipelines;
    }

    /**
     * get the default pipeline for all users
     */
    public function getDefaultPipelineAllUsers()
    {

        return $this->all();
    }

    /**
     * get pipeline by user id
     */
    public function getPipelinesForUser($userId)
    {
        $pipelines = $this->userRepository->with('leadPipelines')->find($userId);
        $pipelines = $pipelines->leadPipelines ? $pipelines->leadPipelines : [];

        return $pipelines;
    }

    /**
     * get all pipelines for user id
     */
    public function getAllUserForPipelineId($pipelineId)
    {
        return $this->where('id', $pipelineId)->with('users')->first();
    }
}
