<?php

use Illuminate\Support\Facades\Route;
use Webkul\Goals\Http\Controllers\GoalsController;

//Route::prefix('goals')->group(function () {});
Route::group([
    'middleware' => ['web', 'admin_locale'],
    'prefix'     => config('app.admin_path')
], function () {
    Route::get('goals', [GoalsController::class, 'index'])->name('admin.goals.index');
    Route::post("goals", [GoalsController::class, "store"])->name("admin.goals.store");
    Route::put("goals/{id}", [GoalsController::class, "update"])->name("admin.goals.update");
    Route::get("goal/{id}", [GoalsController::class, "show"])->name("admin.goals.show");
});