<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Surveyor\Http\Controllers\BranchController;
use Modules\Surveyor\Http\Controllers\SurveyorController;

/*
|--------------------------------------------------------------------------
| ROUTE MODUL Surveyor (konvensi core: api/v1 + auth:sanctum)
|--------------------------------------------------------------------------
| Middleware permission:feature:capability (gate per aksi).
| Modul ini tidak expose endpoint Vat — lihat spine-vat untuk itu.
|
|   /api/v1/surveyors
|     GET    /                              surveyor:view
|     POST   /                              surveyor:create
|     GET    /{id}                          surveyor:view
|     PUT    /{id}                          surveyor:edit
|     DELETE /{id}                          surveyor:delete
|     GET    /{id}/activity-logs            surveyor:view
|     GET    /{id}/branches                 branch:view    (nested)
|
|   /api/v1/branches
|     GET    /                              branch:view
|     POST   /                              branch:create
|     GET    /{id}                          branch:view
|     PUT    /{id}                          branch:edit
|     DELETE /{id}                          branch:delete
|     GET    /{id}/activity-logs            branch:view
*/

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('surveyors')->group(function () {
        Route::get('/', [SurveyorController::class, 'index'])->middleware('permission:surveyor:view');
        Route::post('/', [SurveyorController::class, 'store'])->middleware('permission:surveyor:create');
        Route::get('/{id}', [SurveyorController::class, 'show'])->whereNumber('id')->middleware('permission:surveyor:view');
        Route::put('/{id}', [SurveyorController::class, 'update'])->whereNumber('id')->middleware('permission:surveyor:edit');
        Route::get('/{id}/activity-logs', [SurveyorController::class, 'activityLogs'])->whereNumber('id')->middleware('permission:surveyor:view');
        Route::get('/{id}/branches', [SurveyorController::class, 'branches'])->whereNumber('id')->middleware('permission:branch:view');
        Route::delete('/{id}', [SurveyorController::class, 'destroy'])->whereNumber('id')->middleware('permission:surveyor:delete');
    });

    Route::prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->middleware('permission:branch:view');
        Route::post('/', [BranchController::class, 'store'])->middleware('permission:branch:create');
        Route::get('/{id}', [BranchController::class, 'show'])->whereNumber('id')->middleware('permission:branch:view');
        Route::put('/{id}', [BranchController::class, 'update'])->whereNumber('id')->middleware('permission:branch:edit');
        Route::get('/{id}/activity-logs', [BranchController::class, 'activityLogs'])->whereNumber('id')->middleware('permission:branch:view');
        Route::delete('/{id}', [BranchController::class, 'destroy'])->whereNumber('id')->middleware('permission:branch:delete');
    });
});
