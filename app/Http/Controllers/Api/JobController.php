<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Services\Api\JobFilterService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    protected $filterService;

    public function __construct(JobFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function index(Request $request)
    {
        $query = $this->filterService->filter($request->input('filter', ''));

        $query->with(['languages', 'locations', 'categories', 'attributes.attribute']);
        
        $perPage = $request->input('per_page', 15);
        $jobs = $query->paginate($perPage);
        
        return JobResource::collection($jobs);
    }
}