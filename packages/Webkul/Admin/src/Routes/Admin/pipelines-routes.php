<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Settings\PipelineController;


Route::group(['middleware' => ['user']], function () {
    /**
     * Pipelines Routes.
     */
    Route::controller(PipelineController::class)->prefix('pipelines')->group(function () {
        Route::get('', 'index')->name('admin.settings.pipelines.index');

        Route::get('create', 'create')->name('admin.settings.pipelines.create');

        Route::post('create', 'store')->name('admin.settings.pipelines.store');

        Route::get('edit/{id?}', 'edit')->name('admin.settings.pipelines.edit');

        Route::post('edit/{id}', 'update')->name('admin.settings.pipelines.update');

        Route::delete('{id}', 'destroy')->name('admin.settings.pipelines.delete');

        Route::get('for-userid', 'getPipelinesForUser')->name('admin.settings.pipelines.getPipelinesForUser');
    });
});
