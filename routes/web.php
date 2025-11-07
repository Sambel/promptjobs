<?php

use App\Http\Controllers\JobController;
use Illuminate\Support\Facades\Route;

Route::get('/', [JobController::class, 'index'])->name('jobs.index');
Route::get('/companies', [JobController::class, 'companies'])->name('companies.index');
Route::get('/companies/{company}', [JobController::class, 'companyJobs'])->name('companies.jobs');
Route::get('/companies/{company}/{job}', [JobController::class, 'show'])->name('jobs.show');
Route::get('/apply/{job}', [JobController::class, 'redirectToApply'])->name('jobs.apply');
