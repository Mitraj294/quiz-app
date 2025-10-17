<div data-fragment="topics" class="space-y-6">
    @foreach($topics as $topic)
    <div class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg border border-gray-200">

        <h3 class="text-2xl font-bold mb-4">{{ $topic->name }}</h3>

        <div class="mb-6">
            <p class="text-gray-800">{{ $topic->description ?? $topic->name }}</p>
        </div>

       <div class="grid grid-cols-1 md:grid-cols-2  gap-4">
        <div class="mt-8">
            <h4 class="text-lg font-semibold mb-4">Subtopic</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                        @foreach($topic->children as $sub)
                            <div>
                                <button type="button" data-fragment-url="{{ route('admin.analytics.topic.fragment', $sub->id) }}" class="block text-left w-full p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition topic-link">
                                    <h5 class="font-semibold mb-2">{{ $sub->name }}</h5>
                                    <p class="text-sm text-gray-700">{{ $sub->description ?? $sub->name }}</p>
                                </button>
                            
                            </div>
                        @endforeach
            </div>
        </div>

        <div class="mt-8">
            <h4 class="text-lg font-semibold mb-4">Related Quizzes</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                // fetch related quizzes via topicables (safe fallback)
                $topicableIds = \Illuminate\Support\Facades\DB::table('topicables')->where('topic_id', $topic->id)->pluck('topicable_id')->all();
                $relatedQuizzes = \App\Models\Quiz::whereIn('id', $topicableIds)->get();
                @endphp

                @forelse($relatedQuizzes as $quiz)
                <div class="border border-gray-300 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h5 class="font-semibold mb-2">{{ $quiz->title ?? $quiz->name }}</h5>
                            <div class="flex items-center gap-4 text-xs text-gray-500 mb-2">
                                <span> Total: {{ $quiz->total_marks ?? 0 }} marks</span>
                                <span>Pass: {{ $quiz->pass_marks ?? 0 }} marks</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-3">
                        <a href="{{ route('quizzes.show', $quiz->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View Details</a>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-600">No quizzes found for this topic.</p>
                @endforelse
            </div>
        </div>
       </div>
    </div>
    @endforeach

    <div class="mt-4">
        {{ $topics->links() }}
    </div>
</div>