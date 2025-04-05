<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/jobs', [\App\Http\Controllers\Api\JobController::class, 'index']);
