<?php

namespace Webkul\Goals\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Webkul\Admin\DataGrids\Settings\UserDataGrid;
use Webkul\Admin\Helpers\Reporting\Lead;
use Webkul\Goals\Repositories\GoalsRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\User\Repositories\GroupRepository;
use Webkul\User\Repositories\RoleRepository;
use Webkul\User\Repositories\UserRepository;

class GoalsController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        protected GoalsRepository $goalsRepository,
        protected UserRepository $usersRepository,
        protected RoleRepository $roleRepository,
        protected GroupRepository $groupRepository,
        protected PipelineRepository $pipelineRepository,
        protected LeadRepository $leadRepository,
        protected Lead $leadReporting,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = $this->usersRepository->with('goals')->get();
        if (request()->ajax()) {
            return datagrid(UserDataGrid::class)->process();
        }
        $roles = $this->roleRepository->all();
        $groups = $this->groupRepository->all();
        $pipelines = $this->pipelineRepository->all();
        $goals = $this->goalsRepository->getAllGoals();

        // dd($goals);
        return view('goals::index', compact('roles', 'groups', 'pipelines', 'users', 'goals'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validor = Validator::make($request->all(), [
            'user_id'      => 'required|exists:users,id',
            'pipeline_id'  => 'required|exists:lead_pipelines,id',
            'date_end'     => 'required|date',
            'date_start'   => 'required|date|after:start_date',
            'target_value' => 'required',
        ]);
        if ($validor->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validor->errors(),
            ], 420);
        }
        $data = $request->all();
        $validateDate = $this->goalsRepository->existsGoalInDateRange($data);
        //dd($validateDate);
        if ($validateDate) {
            return response()->json(
                [
                    'success'=>false,
                    "message"=>'Goal already exists in the selected date range: '.$validateDate->end_date." to ".$validateDate->start_date,
                ]
            );
        }
        try {
            $creted = $this->goalsRepository->create([
                'user_id'      => $data['user_id'],
                'pipeline_id'  => $data['pipeline_id'],
                'date_start'   => $data['date_start'],
                'date_end'     => $data['date_end'],
                'target_value' => $data['target_value'],
            ]);
            if (! $creted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong',
                ], 500);
            }

            return response()->json([
                "success"=>true,
                "message"=>"Goal Created Successfully",
                "data"=>$creted,
            ], 200);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->goalsRepository->find($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $validor = Validator::make($request->all(), [
                'user_id'     => 'required|exists:users,id',
                'pipeline_id' => 'required|exists:lead_pipelines,id',
                'date_end'    => 'required|date',
                'date_start'  => 'required|date|after:start_date',
            ]);
            if ($validor->fails()) {
                return redirect()->back()->with('error', 'Validation error');
            }
            $data = $request->all();
            $goal = $this->goalsRepository->find($data['id']);
            if (! $goal) {
                return redirect()->back()->with('error', "Don't exits");
            }
            $updated = $this->goalsRepository->update([
                'user_id'      => $data['user_id'],
                'pipeline_id'  => $data['pipeline_id'],
                'start_date'   => $data['date_start'],
                'end_date'     => $data['date_end'],
                'target_value' => $data['target_value'],
            ], $data['id']);
            if ($updated[1]) {
                return redirect()->back()->with('success', 'Create');
            }

            return redirect()->back()->with('error', 'Error!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Don't exits");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $goal = $this->goalsRepository->delete($id);
        if ($goal[1]) {
            return redirect()->back()->with('success', 'Objetive delete');
        } else {
            return redirect()->back()->with('error', 'Error!');
        }
    }

    /**
     * Get users statitics
     */
    public function statisticsUser(Request $request)
    {
        // return response()->json(["success"=>false,"errors"=>"antra"],420);
        $validator = Validator::make($request->all(), [
            'userId'    => 'required',
            'pipelineId' => 'required',
            'date_start' => 'required',
            'date_end'  => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => 'validation errors', 'erros' => $validator->errors()], 420);
        }
        $data = $request->all();

        return $this->statisticsUserResult($data['userId'], $data['pipelineId'], $data['date_start'], $data['date_end']);
    }

    public function statisticsUserResult($userId, $pipelineId, $date_start, $date_end)
    {
        try {

            $valueGoal = $this->goalsRepository->userStatitics([
                'user_id'     => $userId,
                'pipeline_id' => $pipelineId,
                'start_date'  => $date_start,
                'end_date'    => $date_end,
            ]);

            $leadsWonValueSum = $this->leadReporting->getTotalWonLeadValueForPipelineAndUserId($userId, $pipelineId, $date_start, $date_end);
            $percentageAchieved = ((float) $leadsWonValueSum * 100) / $valueGoal;
            $missingPercentage = 100 - $percentageAchieved;

            $statistics = [
                'name'                => 'Proceso de objetivo',
                'percentage_achieved' => round($percentageAchieved, 2),
                'missing_percentage'  => round($missingPercentage, 2),
                'value_goal'          => $valueGoal,
                'leads_won_value_sum' => $leadsWonValueSum,
            ];

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
