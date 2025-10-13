<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Topic management (simple CRUD: list, create, show)
    Route::get('/topics', [\App\Http\Controllers\TopicController::class, 'index'])->name('topics.index');
    Route::post('/topics', [\App\Http\Controllers\TopicController::class, 'store'])->name('topics.store');
    Route::get('/topics/{topic}', [\App\Http\Controllers\TopicController::class, 'show'])->name('topics.show');
});

// Admin-only route to create quizzes (placeholder)
Route::get('/quizzes/create', function () {
    return view('quizzes.create');
})->middleware(['auth', 'role:admin'])->name('quizzes.create');

require __DIR__.'/auth.php';
