<?php

use App\Http\Controllers\Admin\JobImportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin_user'])->prefix('admin/import-jobs')->name('admin.job-import.')->group(function () {
    Route::get('/', [JobImportController::class, 'index'])->name('index');
    Route::post('/csv', [JobImportController::class, 'upload'])->middleware('throttle:5,1')->name('upload');
    Route::get('/{batch}', [JobImportController::class, 'show'])->whereNumber('batch')->name('show');
});

Route::middleware(['auth', 'admin_user'])->prefix('admin/custom-jobs')->name('admin.custom-jobs.')->group(function () {
    Route::get('/create', [\App\Http\Controllers\Admin\CustomJobController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\CustomJobController::class, 'store'])->name('store');
});
