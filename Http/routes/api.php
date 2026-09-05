<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
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
||     GET    /{id}/activity-logs            surveyor:view
|*/

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('surveyors')->group(function () {
        Route::get('/', [SurveyorController::class, 'index'])->middleware('permission:surveyor:view|surveyor:view-connected');
        Route::post('/', [SurveyorController::class, 'store'])->middleware('permission:surveyor:create');
        Route::get('/{id}', [SurveyorController::class, 'show'])->whereNumber('id')->middleware('permission:surveyor:view|surveyor:view-connected');
        Route::put('/{id}', [SurveyorController::class, 'update'])->whereNumber('id')->middleware('permission:surveyor:edit');
        Route::get('/{id}/activity-logs', [SurveyorController::class, 'activityLogs'])->whereNumber('id')->middleware('permission:surveyor:view|surveyor:view-connected');
        Route::get('/{id}/branches', [SurveyorController::class, 'branches'])->whereNumber('id')->middleware('permission:branch:view');
        Route::get('/{id}/staffs', [SurveyorController::class, 'staffs'])->whereNumber('id')->middleware('permission:surveyor:view|surveyor:view-connected');
        Route::delete('/{id}', [SurveyorController::class, 'destroy'])->whereNumber('id')->middleware('permission:surveyor:delete');
    });
});
