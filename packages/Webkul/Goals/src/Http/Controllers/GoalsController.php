<?php

namespace Webkul\Goals\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Webkul\Admin\DataGrids\Settings\UserDataGrid;
use Webkul\Goals\Contracts\Goals;
use Webkul\Goals\Repositories\GoalsRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\User\Models\User;
use Webkul\User\Repositories\GroupRepository;
use Webkul\User\Repositories\RoleRepository;
use Webkul\User\Repositories\UserRepository;

class GoalsController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function __construct(
        protected  GoalsRepository $goalsRepository,
        protected UserRepository $usersRepository,
        protected RoleRepository $roleRepository,
        protected GroupRepository $groupRepository,
        protected PipelineRepository $pipelineRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = $this->usersRepository->with("goals")->get();
        if (request()->ajax()) {
            return datagrid(UserDataGrid::class)->process();
        }
        $roles = $this->roleRepository->all();
        $groups = $this->groupRepository->all();
        $pipelines = $this->pipelineRepository->all();
        $goals = $this->goalsRepository->getAllGoals();
        //dd($goals);
        return view('goals::index', compact('roles', 'groups', 'pipelines', 'users', "goals"));
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
            'user_id' => 'required|exists:users,id',
            'pipeline_id' => 'required|exists:lead_pipelines,id',
            'date_end' => 'required|date',
            'date_start' => 'required|date|after:start_date'
        ]);
        if ($validor->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validor->errors()
            ], 420);
        }
        $data = $request->all();
        try {
            $creted = $this->goalsRepository->create([
                'user_id' => $data['user_id'],
                'pipeline_id' => $data['pipeline_id'],
                'date_start' => $data['date_start'],
                'date_end' => $data['date_end']
            ]);
            if (!$creted) {
                return response()->json([
                    'success' => false,
                    'message' => "Something went wrong"
                ], 500);
            }
            return redirect()->back()->with("success", "Goal Created Successfully");
        } catch (\Exception $e) {
            return redirect()->back()->with("error", "Something went wrong");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id) {}

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id) {}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {}
}
