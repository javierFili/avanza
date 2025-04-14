<?php

use Illuminate\Support\Facades\Route;
use Webkul\Goals\Http\Controllers\GoalsController;

// Route::prefix('goals')->group(function () {});
Route::group([
    'middleware' => ['web', 'api', 'admin_locale'],
    'prefix'     => config('app.admin_path'),
], function () {
    Route::get('goals', [GoalsController::class, 'index'])->name('admin.goals.index');
    Route::post('goals', [GoalsController::class, 'store'])->name('admin.goals.store');
    Route::put('goals/{id}', [GoalsController::class, 'update'])->name('admin.goals.update');
    Route::get('goal/{id}', [GoalsController::class, 'show'])->name('admin.goals.show');
    Route::post('goal/{id}', [GoalsController::class, 'destroy'])->name('admin.goals.delete');
    /**This seccion is for statistics of users grafighs */
    Route::get('goal/user/statistics', [GoalsController::class, 'statisticsUser'])->name('admin.goals.user.statistics');
});
