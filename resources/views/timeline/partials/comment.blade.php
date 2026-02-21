<div class="comment-item d-flex gap-2 mb-2" data-comment-id="{{ $comment->id }}">
    @if($comment->author->avatar_url ?? null)
        <img src="{{ $comment->author->avatar_url }}"
             alt="{{ e($comment->author->name ?? 'Unknown') }}"
             class="rounded-circle flex-shrink-0"
             style="width: 32px; height: 32px; object-fit: cover;">
    @else
        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0"
             style="width: 32px; height: 32px; font-size: 14px;">
            {{ strtoupper(substr($comment->author->name ?? 'A', 0, 1)) }}
        </div>
    @endif
    <div class="flex-grow-1">
        <div class="bg-light rounded p-2">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="mb-0 fs-6 fw-semibold">{{ e($comment->author->name ?? 'Unknown') }}</h6>
                @if($comment->can_delete ?? ($comment->user_id === Auth::id()))
                    <form action="{{ route('timeline.delete-comment', $comment->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 fs-6"
                                onclick="return confirm('Hapus komentar ini?');">✕</button>
                    </form>
                @endif
            </div>
            <p class="mb-0 small text-break">{{ e($comment->content) }}</p>
        </div>
        <small class="text-muted">{{ $comment->time_ago }}</small>
    </div>
</div>
