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
    // Only admins should be able to create topics
    Route::post('/topics', [\App\Http\Controllers\TopicController::class, 'store'])->name('topics.store')->middleware('role:admin');
    Route::get('/topics/{topic}', [\App\Http\Controllers\TopicController::class, 'show'])->name('topics.show');
    // Note: quiz creation and management routes are restricted to admins (declared below)
    
    // Quiz routes - list all quizzes
    Route::get('/quizzes', [\App\Http\Controllers\QuizController::class, 'index'])->name('quizzes.index');
});

// Admin-only quiz management routes (specific routes MUST come before dynamic {quiz} route)
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin-only quiz management
    Route::get('/quizzes/create', [\App\Http\Controllers\QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [\App\Http\Controllers\QuizController::class, 'store'])->name('quizzes.store');
    Route::delete('/quizzes/{quiz}', [\App\Http\Controllers\QuizController::class, 'destroy'])->name('quizzes.destroy');
    // Admin-only question management for topics
    Route::get('/topics/{topic}/questions/create', [\App\Http\Controllers\QuestionController::class, 'create'])->name('topics.questions.create');
    Route::post('/topics/{topic}/questions', [\App\Http\Controllers\QuestionController::class, 'store'])->name('topics.questions.store');
    // Media upload route for question media
    Route::post('/media/upload', [\App\Http\Controllers\MediaController::class, 'upload'])->name('media.upload');
        // Admin: create a question and attach it directly to a quiz
        Route::get('/quizzes/{quiz}/questions/create', [\App\Http\Controllers\QuizController::class, 'createQuestion'])->name('quizzes.questions.create');
        Route::post('/quizzes/{quiz}/questions', [\App\Http\Controllers\QuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    // Admin: attach existing questions to a quiz
    Route::get('/quizzes/{quiz}/questions/select', [\App\Http\Controllers\QuizController::class, 'selectQuestions'])->name('quizzes.questions.select');
    Route::post('/quizzes/{quiz}/questions/attach', [\App\Http\Controllers\QuizController::class, 'attachQuestions'])->name('quizzes.questions.attach');
});

// Quiz show route - MUST come after /quizzes/create to avoid route conflict
Route::middleware('auth')->group(function () {
    Route::get('/quizzes/{quiz}', [\App\Http\Controllers\QuizController::class, 'show'])->name('quizzes.show');
});

require __DIR__.'/auth.php';
