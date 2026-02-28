@if($posts->count() > 0)
    <div class="posts-container" data-next-page="{{ $posts->hasMorePages() ? $posts->currentPage() + 1 : 'null' }}">
        @foreach($posts as $post)
            @include('timeline.partials.post', ['post' => $post])
        @endforeach
    </div>

    @if($posts->hasMorePages())
        <div class="text-center mt-4">
            <button class="btn btn-outline-primary load-more-btn" data-page="{{ $posts->currentPage() + 1 }}">
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                <span class="btn-text">Muat lebih banyak</span>
            </button>
        </div>
    @endif
@else
    <div class="text-center py-5">
        <div class="mb-3" style="font-size: 64px;">📝</div>
        <h5 class="text-muted">Belum ada postingan</h5>
        <p class="text-muted">Mulai bagikan momen dengan pasanganmu!</p>
    </div>
@endif
