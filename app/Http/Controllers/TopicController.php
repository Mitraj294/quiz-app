<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    public function index()
    {
        $topics = Topic::orderBy('name')->get();
        return view('topics.index', [
            'topics' => $topics,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:topics,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        Topic::create($data);

        // If creating a sub-topic, redirect back to parent topic
        if ($data['parent_id']) {
            return redirect()->route('topics.show', $data['parent_id'])
                ->with('success', 'Sub-topic created successfully!');
        }

        return redirect()->route('topics.index')->with('success', 'Topic created successfully!');
    }

    public function show(Topic $topic)
    {
        return view('topics.show', [
            'topic' => $topic->load('questions','quizzes'),
        ]);
    }
}
