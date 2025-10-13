<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'appName' => config('app.name'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin-only route to create quizzes (placeholder)
Route::get('/quizzes/create', function () {
    return Inertia::render('Quizzes/Create');
})->middleware(['auth', 'role:admin'])->name('quizzes.create');

define('PROFILE_ROUTE', '/profile');

Route::middleware('auth')->group(function () {
    Route::get(PROFILE_ROUTE, [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch(PROFILE_ROUTE, [ProfileController::class, 'update'])->name('profile.update');
    Route::delete(PROFILE_ROUTE, [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Topic management (simple CRUD: list, create, show)
    Route::get('/topics', [\App\Http\Controllers\TopicController::class, 'index'])->name('topics.index');
    Route::post('/topics', [\App\Http\Controllers\TopicController::class, 'store'])->name('topics.store');
    Route::get('/topics/{topic}', [\App\Http\Controllers\TopicController::class, 'show'])->name('topics.show');
});

require __DIR__.'/auth.php';
