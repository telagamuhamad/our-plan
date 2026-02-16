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
