<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Webkul\Admin\Helpers\Dashboard;
use Webkul\Lead\Repositories\PipelineRepository;

class DashboardController extends Controller
{
    /**
     * Request param functions
     *
     * @var array
     */
    protected $typeFunctions = [
        'over-all'             => 'getOverAllStats',
        'revenue-stats'        => 'getRevenueStats',
        'total-leads'          => 'getTotalLeadsStats',
        'revenue-by-sources'   => 'getLeadsStatsBySources',
        'revenue-by-types'     => 'getLeadsStatsByTypes',
        'top-selling-products' => 'getTopSellingProducts',
        'top-persons'          => 'getTopPersons',
        'open-leads-by-states' => 'getOpenLeadsByStates',
        'user-proccess-states' => 'getUserProccessStates',
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected Dashboard $dashboardHelper,
        protected PipelineRepository $pipelineRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $userId = Auth::user()->id;
        $pipelines = $this->pipelineRepository->getAllPipelinesByUserId($userId);
        return view('admin::dashboard.index')->with([
            'pipelines' => $pipelines,
            'startDate' => $this->dashboardHelper->getStartDate(),
            'endDate'   => $this->dashboardHelper->getEndDate(),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $stats = $this->dashboardHelper->{$this->typeFunctions[request()->query('type')]}();
        return response()->json([
            'statistics' => $stats,
            'date_range' => $this->dashboardHelper->getDateRange(),
        ]);
    }
}
