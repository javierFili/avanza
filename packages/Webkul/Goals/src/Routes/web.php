<?php

use Illuminate\Support\Facades\Route;
use Webkul\Goals\Http\Controllers\GoalsController;

Route::prefix('goals')->group(function () {
    Route::get('', [GoalsController::class, 'index'])->name('admin.goals.index');
});
