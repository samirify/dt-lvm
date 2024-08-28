<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ProjectController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});


Route::group([
    'middleware' => 'api',
], function ($router) {
    Route::get('/initialise', [MainController::class, 'initialise'])->name('main_data.initialise');

    Route::get('/project-data/{code}', [ProjectController::class, 'projectData'])->name('project_data');
    Route::get('/projects', [ProjectController::class, 'projectsList'])->name('projects_list');
    Route::post('/deploy-project', [ProjectController::class, 'initiateProjectDeployment'])->name('initiate_project_deployment');
    Route::post('/re-deploy-project/{id}', [ProjectController::class, 'reDeployProject'])->where('id', '[0-9]+')->name('re_deploy_project');
    Route::get('/project-status/{id}', [ProjectController::class, 'projectStatus'])->where('id', '[0-9]+')->name('project_status');
    Route::get('/export-project-log/{id}', [ProjectController::class, 'exportProjectDeploymentLog'])->where('id', '[0-9]+')->name('export_project_deployment_log');

    Route::get('/help/topic/{code}', [HelpController::class, 'getTopic'])->name('help.get_topic');
});
