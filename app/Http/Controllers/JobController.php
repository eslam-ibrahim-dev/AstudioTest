<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Services\JobFilterService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class JobController extends Controller
{

    protected $jobFilterService;

    public function __construct(JobFilterService $jobFilterService)
    {
        $this->jobFilterService = $jobFilterService;
    }
    public function filterJobs(Request $request)
    {
        $query = Job::query();

        $filters = $request->input('filter');
        $query->with(['languages', 'locations', 'attributes.attribute']);

        if ($filters) {
            $this->jobFilterService->applyFilters($query, $filters);
        }

        $jobs = $query->get();

        return response()->json($jobs);
    }
}
