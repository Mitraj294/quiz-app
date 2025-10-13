<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    public function index()
    {
        $topics = Topic::orderBy('name')->get();
        return Inertia::render('Topics/Index', [
            'topics' => $topics,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:topics,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

    Topic::create($data);

    return redirect()->route('topics.index')->with('success', 'Topic created.');
    }

    public function show(Topic $topic)
    {
        return Inertia::render('Topics/Show', [
            'topic' => $topic->load('questions','quizzes'),
        ]);
    }
}
