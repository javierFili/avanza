<?php

namespace Webkul\Admin\Helpers\Reporting;

use Webkul\Goals\Repositories\GoalsRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\User\Repositories\UserRepository;

class Goals extends AbstractReporting
{
    public function __construct(
        protected GoalsRepository $goalsRepository,
        protected Lead $leadReporting,
        protected PipelineRepository $pipelineRepository,
        protected UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    public function getUserProccess()
    {
        return $this->statisticsUser($this->pipelineId, $this->startDate, $this->endDate);
    }

    /**
     * Get users statitics
     */
    public function statisticsUser($pipelineId, $startDate, $endDate)
    {
        // get all user in pipeline, and render any users...
        $pipeline = $this->pipelineRepository->getAllUserForPipelineId($pipelineId);
        $result = [];
        if ($pipeline->users) {
            $users = $pipeline->users;
            foreach ($users as $user) {
                $userStatistics = $this->statisticsUserResult($pipelineId, $startDate, $endDate, $user->id);
                if ($userStatistics != false) {
                    array_push($result, $userStatistics);
                }
            }
        }
        return $result;
    }

    public function statisticsUserResult($pipelineId, $date_start, $date_end, $userId)
    {
        try {
            $goals = $this->goalsRepository->userStatitics([
                'user_id'     => $userId,
                'pipeline_id' => $pipelineId,
                'start_date'  => $date_start,
                'end_date'    => $date_end,
            ]);
            if ($goals == false) {
                return false;
            }
            $statistics = [];
            foreach($goals as $goal){
                $valueGoal = $goal->target_value;
                $date_start = $goal->start_date;
                $date_end = $goal->end_date;
                $leadsWonValueSum = $this->leadReporting->getTotalWonLeadValueForPipelineAndUserId($pipelineId, $userId, $date_start, $date_end);
                $percentageAchieved = $valueGoal > 0 ? ((float) $leadsWonValueSum * 100) / $valueGoal : 0;
                $missingPercentage = $percentageAchieved >= 100 ? 0 : 100 - $percentageAchieved;
                $user = $this->userRepository->find($userId);
                $statistics[] = [
                    'userFullName'        => $user->name,
                    'name'                => 'Proceso de objetivo',
                    'percentage_achieved' => round($percentageAchieved, 2),
                    'missing_percentage'  => round($missingPercentage, 2),
                    'value_goal'          => $valueGoal,
                    'leads_won_value_sum' => $leadsWonValueSum,
                    'date_goal'           => $goal->start_date.'  '.$goal->end_date
                ];
            }
            return response()->json(['success' => true, 'statistics' => $statistics, 'date_range' => 'rango'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success'           => false,
                'error'             => 'Server error',
                'exception_message' => $e->getMessage(),
            ], 500);
        }
    }
}
