<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MdaController;
use App\Http\Controllers\DevelopmentPartnerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MonitoringController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // MDAs
    Route::resource('mdas', MdaController::class);

    // Development Partners
    Route::resource('development-partners', DevelopmentPartnerController::class);

    // Projects
    Route::resource('projects', ProjectController::class);

    // Monitoring
    Route::resource('monitorings', MonitoringController::class);

});

require __DIR__.'/auth.php';