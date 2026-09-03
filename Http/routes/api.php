<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\BranchController;
use Modules\Customer\Http\Controllers\CustomerController;

/*
|--------------------------------------------------------------------------
| ROUTE MODUL Customer (konvensi core: api/v1 + auth:sanctum)
|--------------------------------------------------------------------------
| Middleware permission:feature:capability (gate per aksi).
| Modul ini tidak expose endpoint Vat — lihat spine-vat untuk itu.
|
|   /api/v1/customers
|     GET    /                              customer:view
|     POST   /                              customer:create
|     GET    /{id}                          customer:view
|     PUT    /{id}                          customer:edit
|     DELETE /{id}                          customer:delete
|     GET    /{id}/activity-logs            customer:view
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
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->middleware('permission:customer:view');
        Route::post('/', [CustomerController::class, 'store'])->middleware('permission:customer:create');
        Route::get('/{id}', [CustomerController::class, 'show'])->whereNumber('id')->middleware('permission:customer:view');
        Route::put('/{id}', [CustomerController::class, 'update'])->whereNumber('id')->middleware('permission:customer:edit');
        Route::get('/{id}/activity-logs', [CustomerController::class, 'activityLogs'])->whereNumber('id')->middleware('permission:customer:view');
        Route::get('/{id}/branches', [CustomerController::class, 'branches'])->whereNumber('id')->middleware('permission:branch:view');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->whereNumber('id')->middleware('permission:customer:delete');
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
