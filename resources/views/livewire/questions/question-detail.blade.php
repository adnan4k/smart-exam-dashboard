<div>
    <h2>Question Details</h2>
    <p><strong>Question:</strong> {{ $question->question_text }}</p>
    <p><strong>Subject:</strong> {{ $question->subject->name }}</p>
    <p><strong>Year Group:</strong> {{ $question->yearGroup->year }}</p>
    <p><strong>Type:</strong> {{ ucfirst($question->type->name) }}</p>
    <p><strong>Choices:</strong></p>
    <ul>
        @foreach ($question->choices as $choice)
            <li>{{ $choice->text }}</li>
        @endforeach
    </ul>
    <p><strong>Answer:</strong> {{ $question->answerText }}</p>
    <p><strong>Explanation:</strong> {{ $question->explanation }}</p>
    <button @click="$dispatch('close-detail-modal')" class="btn btn-secondary">Close</button>
</div>
