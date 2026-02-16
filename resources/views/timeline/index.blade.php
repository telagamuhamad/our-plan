@extends('layouts.app')

@section('title', 'Timeline - Our Plan')

@section('content')
<div class="timeline-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">💌 Timeline Kita</h4>
            <p class="text-muted mb-0 small">Bagikan momen spesial bersama pasanganmu</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
            + Buat Postingan
        </button>
    </div>

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

    <!-- Posts Feed -->
    <div id="postsFeed">
        @include('timeline.partials.posts', ['posts' => $posts])
    </div>
</div>
@endsection

@push('modals')
@include('timeline.partials.create-post-modal')
@endpush

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

    .load-more-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
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

    // Load more posts
    document.querySelectorAll('.load-more-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const page = this.dataset.page;
            const spinner = this.querySelector('.spinner-border');
            const btnText = this.querySelector('.btn-text');

            spinner.classList.remove('d-none');
            btnText.textContent = 'Memuat...';
            this.disabled = true;

            fetch(`{{ route('timeline.load-more') }}?page=${page}`)
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html;

                        const newPosts = tempDiv.querySelector('.posts-container');
                        const currentContainer = document.querySelector('.posts-container');

                        if (newPosts && currentContainer) {
                            newPosts.querySelectorAll('.post-card').forEach(post => {
                                currentContainer.appendChild(post.cloneNode(true));
                            });

                            // Update next page
                            currentContainer.dataset.nextPage = data.next_page;

                            // Reattach event listeners
                            attachEventListeners();
                        }

                        // Remove load more button if no more pages
                        if (!data.next_page) {
                            this.closest('.text-center').remove();
                        } else {
                            this.dataset.page = data.next_page;
                            spinner.classList.add('d-none');
                            btnText.textContent = 'Muat lebih banyak';
                            this.disabled = false;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    spinner.classList.add('d-none');
                    btnText.textContent = 'Muat lebih banyak';
                    this.disabled = false;
                });
        });
    });

    // Reattach event listeners after dynamic content
    function attachEventListeners() {
        // Comment toggle
        document.querySelectorAll('.comment-toggle-btn:not([data-attached])').forEach(btn => {
            btn.setAttribute('data-attached', 'true');
            btn.addEventListener('click', function() {
                const postId = this.dataset.postId;
                const commentsSection = document.getElementById('comments-' + postId);
                commentsSection.classList.toggle('d-none');
                this.classList.toggle('btn-outline-secondary');
                this.classList.toggle('btn-secondary');
            });
        });

        // React buttons
        document.querySelectorAll('.react-btn:not([data-attached])').forEach(btn => {
            btn.setAttribute('data-attached', 'true');
            btn.addEventListener('click', function() {
                const postId = this.dataset.postId;
                const emoji = this.dataset.emoji;

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
    }

    // Form submission with loading state
    const createPostForm = document.getElementById('createPostForm');
    if (createPostForm) {
        createPostForm.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            const spinner = btn.querySelector('.spinner-border');
            const btnText = btn.querySelector('.btn-text');

            spinner.classList.remove('d-none');
            btnText.textContent = 'Memposting...';
            btn.disabled = true;
        });
    }
});
</script>
