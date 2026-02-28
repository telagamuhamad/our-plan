@extends('layouts.app')

@section('title', 'Postingan - Our Plan')

@section('content')
<div class="timeline-show-page">
    <!-- Back Button -->
    <a href="{{ route('timeline.index') }}" class="btn btn-link text-decoration-none mb-3 ps-0">
        ← Kembali ke Timeline
    </a>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Single Post -->
    <div class="post-detail">
        @include('timeline.partials.post', ['post' => $post])
    </div>

    <!-- All Comments -->
    <div class="comments-section mt-4">
        <h5 class="mb-3">Komentar ({{ $post->comments_count ?? 0 }})</h5>

        @if(isset($post->comments) && $post->comments->count() > 0)
            <div class="comments-list mb-3">
                @foreach($post->comments as $comment)
                    @include('timeline.partials.comment', ['comment' => $comment, 'postId' => $post->id])
                @endforeach
            </div>
        @else
            <p class="text-muted">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
        @endif

        <!-- Add Comment Form -->
        <form action="{{ route('timeline.comment', $post->id) }}" method="POST" class="mt-3">
            @csrf
            <div class="input-group">
                <textarea class="form-control" name="content" rows="2"
                          placeholder="Tulis komentar..." maxlength="1000" required></textarea>
                <button type="submit" class="btn btn-primary">Kirim</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .post-card {
        border: 1px solid #e9ecef;
    }

    .reaction-summary .badge {
        cursor: pointer;
        transition: all 0.2s;
    }

    .reaction-summary .badge:hover {
        background-color: #e9ecef !important;
    }

    .comments-section {
        border-top: 1px solid #e9ecef;
        padding-top: 1rem;
    }

    .comment-item .btn-link:hover {
        text-decoration: none;
    }

    .react-btn {
        border: none;
        background: none;
        transition: transform 0.2s;
    }

    .react-btn:hover {
        transform: scale(1.2);
    }

    /* Fix emoji dropdown positioning */
    .dropdown.position-static {
        position: static;
    }

    .reaction-dropdown {
        position: absolute !important;
        left: 0 !important;
        margin-top: 0.5rem !important;
    }

    .post-detail {
        max-width: 100%;
    }

    .comments-list {
        border-top: 1px solid #e9ecef;
        padding-top: 1rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Comment toggle
    document.querySelectorAll('.comment-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const commentsSection = document.getElementById('comments-' + postId);

            commentsSection.classList.toggle('d-none');
            this.classList.toggle('btn-outline-secondary');
            this.classList.toggle('btn-secondary');
        });
    });

    // React buttons
    document.querySelectorAll('.react-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const emoji = this.dataset.emoji;
            const postCard = this.closest('.post-card');
            const reactBtn = postCard.querySelector('[data-bs-toggle="dropdown"]');

            // Send AJAX request
            fetch(`/timeline/react/${postId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ emoji: emoji })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Comment form submission
    document.querySelectorAll('form[action*="comment"]').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.textContent = 'Mengirim...';
            btn.disabled = true;
        });
    });
});
</script>
@endpush
