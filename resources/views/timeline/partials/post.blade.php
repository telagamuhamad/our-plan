@php
    $emojiMap = [
        'heart' => '❤️',
        'laugh' => '😂',
        'wow' => '😮',
        'sad' => '😢',
        'angry' => '😡',
    ];

    $userReactionEmoji = null;
    if ($post->hasUserReacted()) {
        $reaction = $post->getUserReaction();
        $userReactionEmoji = $reaction ? $reaction->emoji : null;
    }
@endphp

<div class="card mb-4 hover-shadow transition post-card" data-post-id="{{ $post->id }}">
    <div class="card-body">
        <!-- Post Header -->
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                     style="width: 40px; height: 40px; font-size: 18px;">
                    {{ strtoupper(substr($post->author->name, 0, 1)) }}
                </div>
                <div>
                    <h6 class="mb-0 fw-semibold">{{ e($post->author->name) }}</h6>
                    <small class="text-muted">{{ $post->time_ago }}</small>
                </div>
            </div>
            @if($post->can_edit ?? ($post->user_id === Auth::id()))
                <div class="dropdown">
                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
                        </svg>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('timeline.edit', $post->id) }}">Edit</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('timeline.destroy', $post->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus postingan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger">Hapus</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endif
        </div>

        <!-- Post Content -->
        <div class="post-content mb-3">
            @if($post->content)
                <p class="mb-2">{{ nl2br(e($post->content)) }}</p>
            @endif

            @if($post->hasAttachment())
                @if($post->post_type === 'photo')
                    <a href="{{ $post->attachment_url }}" target="_blank" class="d-block mb-2">
                        <img src="{{ $post->attachment_url }}" alt="Foto" class="img-fluid rounded" loading="lazy">
                    </a>
                @elseif($post->post_type === 'voice_note')
                    <div class="mb-2">
                        <p class="small text-muted mb-1">🎤 Voice Note</p>
                        <audio controls class="w-100" src="{{ $post->attachment_url }}">
                            Browser Anda tidak mendukung audio.
                        </audio>
                    </div>
                @endif
            @endif
        </div>

        <!-- Post Type Badge -->
        @if($post->post_type !== 'text')
            <span class="badge bg-light text-secondary mb-2">
                @if($post->post_type === 'photo') 📷 Foto
                @elseif($post->post_type === 'voice_note') 🎤 Voice Note
                @endif
            </span>
        @endif

        <!-- Reactions Section -->
        <div class="d-flex justify-content-between align-items-center border-top pt-3">
            <!-- Reaction Summary -->
            <div class="reaction-summary d-flex align-items-center gap-1">
                @foreach($post->reaction_summary as $emoji => $count)
                    @if($count > 0)
                        <span class="badge bg-light border" title="{{ $emojiMap[$emoji] ?? $emoji }}">
                            {{ $emojiMap[$emoji] ?? $emoji }} {{ $count }}
                        </span>
                    @endif
                @endforeach
            </div>

            <!-- Comment Count -->
            @if($post->comments_count > 0)
                <small class="text-muted">
                    {{ $post->comments_count }} {{ $post->comments_count > 1 ? 'komentar' : 'komentar' }}
                </small>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2 mt-3">
            <!-- React Button (Dropdown) -->
            <div class="dropdown position-static">
                <button class="btn btn-sm @if($userReactionEmoji) btn-primary @else btn-outline-primary @endif d-flex align-items-center gap-1"
                        data-bs-toggle="dropdown" type="button">
                    <span>{{ $userReactionEmoji ? ($emojiMap[$userReactionEmoji] ?? '❤️') : '❤️' }}</span>
                    <span>{{ $userReactionEmoji ? 'Suka' : 'Suka' }}</span>
                </button>
                <ul class="dropdown-menu p-2 reaction-dropdown">
                    <li class="d-flex gap-2 flex-wrap justify-content-center">
                        @foreach(['heart' => '❤️', 'laugh' => '😂', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'] as $key => $emoji)
                            <button type="button" class="btn btn-link p-1 fs-5 react-btn position-relative
                                    @if($userReactionEmoji === $key) text-primary @endif"
                                    data-emoji="{{ $key }}" data-post-id="{{ $post->id }}"
                                    title="{{ ucfirst($key) }}">
                                {{ $emoji }}
                                @if($userReactionEmoji === $key)
                                    <span class="position-absolute top-0 start-100 translate-middle">
                                        <span class="badge bg-primary rounded-pill" style="font-size: 8px;">✓</span>
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </li>
                </ul>
            </div>

            <!-- Comment Toggle -->
            <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 comment-toggle-btn"
                    data-post-id="{{ $post->id }}">
                <span>💬</span>
                <span>Komentar</span>
            </button>

            <!-- View Full Post -->
            <a href="{{ route('timeline.show', $post->id) }}" class="btn btn-sm btn-link text-decoration-none ms-auto">
                Lihat selengkapnya
            </a>
        </div>

        <!-- Comments Section (Expandable) -->
        <div class="comments-section mt-3 d-none" id="comments-{{ $post->id }}">
            <!-- Recent Comments -->
            @if($post->recent_comments && $post->recent_comments->count() > 0)
                <div class="comments-list mb-3">
                    @foreach($post->recent_comments as $comment)
                        @include('timeline.partials.comment', ['comment' => $comment, 'postId' => $post->id])
                    @endforeach
                </div>
            @endif

            <!-- Add Comment Form -->
            <form action="{{ route('timeline.comment', $post->id) }}" method="POST" class="comment-form">
                @csrf
                <div class="input-group">
                    <input type="text" name="content" class="form-control" placeholder="Tulis komentar..."
                           maxlength="1000" required>
                    <button type="submit" class="btn btn-primary">Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>
