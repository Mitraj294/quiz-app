<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

if (! defined('PROFILE_PATH')) {
    define('PROFILE_PATH', '/profile');
}

if (! defined('QUIZ_PATH')) {
    define('QUIZ_PATH', '/quizzes/{quiz}');
}

if (! defined('QUESTION_PATH')) {
    define('QUESTION_PATH', '/questions/{question}');
}
if (! defined('TOPIC_PATH')) {
    define('TOPIC_PATH', '/topics/{topic}');
}

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get(PROFILE_PATH, [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch(PROFILE_PATH, [ProfileController::class, 'update'])->name('profile.update');
    Route::delete(PROFILE_PATH, [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Topics (listing and viewing for authenticated users)
    Route::get('/topics', [TopicController::class, 'index'])->name('topics.index');
    Route::get(TOPIC_PATH, [TopicController::class, 'show'])->name('topics.show');

    // Quizzes (listing)
    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
});

// Admin-only routes (keep before dynamic {quiz} public route)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::get(QUIZ_PATH . '/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
    Route::put(QUIZ_PATH, [QuizController::class, 'update'])->name('quizzes.update');
    Route::delete(QUIZ_PATH, [QuizController::class, 'destroy'])->name('quizzes.destroy');

    // Topic -> Question management
    Route::get(TOPIC_PATH . '/questions/create', [QuestionController::class, 'create'])->name('topics.questions.create');
    Route::post(TOPIC_PATH . '/questions', [QuestionController::class, 'store'])->name('topics.questions.store');

    // Topic management (admin)
    Route::post('/topics', [TopicController::class, 'store'])->name('topics.store');
    Route::get(TOPIC_PATH . '/edit', [TopicController::class, 'edit'])->name('topics.edit');
    Route::put(TOPIC_PATH, [TopicController::class, 'update'])->name('topics.update');
    Route::delete(TOPIC_PATH, [TopicController::class, 'destroy'])->name('topics.destroy');

    // Question CRUD
    Route::get(QUESTION_PATH . '/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put(QUESTION_PATH, [QuestionController::class, 'update'])->name('questions.update');
    Route::delete(QUESTION_PATH, [QuestionController::class, 'destroy'])->name('questions.destroy');

    // Quiz <-> Question management
    Route::get(QUIZ_PATH . '/questions/create', [QuizController::class, 'createQuestion'])->name('quizzes.questions.create');
    Route::post(QUIZ_PATH . '/questions', [QuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::get(QUIZ_PATH . '/questions/select', [QuizController::class, 'selectQuestions'])->name('quizzes.questions.select');
    Route::post(QUIZ_PATH . '/questions/attach', [QuizController::class, 'attachQuestions'])->name('quizzes.questions.attach');
    Route::delete(QUIZ_PATH . '/questions/{question}/detach', [QuizController::class, 'detachQuestion'])->name('quizzes.questions.detach');
    Route::get(QUIZ_PATH . '/questions/{question}/edit', [QuizController::class, 'editQuestion'])->name('quizzes.questions.edit');
    Route::put(QUIZ_PATH . '/questions/{question}', [QuizController::class, 'updateQuestion'])->name('quizzes.questions.update');

    // Media upload
    Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');

    // Publish/unpublish
    Route::post(QUIZ_PATH . '/publish', [QuizController::class, 'publish'])->name('quizzes.publish');
});

// Quiz public routes for authenticated users (must be after admin routes)
Route::middleware('auth')->group(function () {
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::get('/quizzes/{quiz}/attempt', [AttemptController::class, 'start'])->name('quizzes.attempt');
    Route::post('/quizzes/{quiz}/submit', [AttemptController::class, 'submit'])->name('quizzes.submit');
});

require __DIR__ . '/auth.php';
