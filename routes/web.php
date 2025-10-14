<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Reusable path constant to avoid duplicated literal
if (! defined('PROFILE_PATH')) {
    define('PROFILE_PATH', '/profile');
}

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get(PROFILE_PATH, [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch(PROFILE_PATH, [ProfileController::class, 'update'])->name('profile.update');
    Route::delete(PROFILE_PATH, [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Topic management (simple CRUD: list, create, show)
    Route::get('/topics', [\App\Http\Controllers\TopicController::class, 'index'])->name('topics.index');
    Route::post('/topics', [\App\Http\Controllers\TopicController::class, 'store'])->name('topics.store');
    Route::get('/topics/{topic}', [\App\Http\Controllers\TopicController::class, 'show'])->name('topics.show');
    Route::get('/topics/{topic}/quizzes/create', [\App\Http\Controllers\QuizController::class, 'create'])->name('quizzes.create');
    
    // Quiz routes - list all quizzes
    Route::get('/quizzes', [\App\Http\Controllers\QuizController::class, 'index'])->name('quizzes.index');
});

// Admin-only quiz management routes (specific routes MUST come before dynamic {quiz} route)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/quizzes/create', [\App\Http\Controllers\QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [\App\Http\Controllers\QuizController::class, 'store'])->name('quizzes.store');
    Route::delete('/quizzes/{quiz}', [\App\Http\Controllers\QuizController::class, 'destroy'])->name('quizzes.destroy');
});

// Quiz show route - MUST come after /quizzes/create to avoid route conflict
Route::middleware('auth')->group(function () {
    Route::get('/quizzes/{quiz}', [\App\Http\Controllers\QuizController::class, 'show'])->name('quizzes.show');
});

require __DIR__.'/auth.php';
