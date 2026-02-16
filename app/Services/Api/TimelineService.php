<?php

namespace App\Services\Api;

use App\Models\PostComment;
use App\Models\TimelinePost;
use App\Repositories\TimelinePostRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TimelineService
{
    protected TimelinePostRepository $repository;

    public function __construct(TimelinePostRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all posts for the authenticated user's couple.
     */
    public function getFeed(int $page = 1, int $perPage = 15): array
    {
        $user = Auth::user();
        if (!$user->couple_id) {
            throw new Exception('User does not belong to a couple.', 403);
        }

        $posts = $this->repository->getCouplePosts($user->couple, $perPage);

        return [
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ],
        ];
    }

    /**
     * Find a specific post.
     */
    public function findPost(int $postId): TimelinePost
    {
        $post = $this->repository->findPostWithRelations($postId);

        if (!$post) {
            throw new Exception('Post not found.', 404);
        }

        $this->verifyPostAccess($post);

        return $post;
    }

    /**
     * Create a new post.
     */
    public function createPost(array $data, $attachment = null): TimelinePost
    {
        $user = Auth::user();
        if (!$user->couple_id) {
            throw new Exception('User does not belong to a couple.', 403);
        }

        DB::beginTransaction();

        try {
            $postData = [
                'post_type' => $data['post_type'],
                'content' => $data['content'] ?? null,
            ];

            if ($attachment) {
                $postData = array_merge($postData, $this->handleFileUpload($attachment, $data['post_type']));
            }

            $post = $this->repository->createPost($user->couple, $user, $postData);

            DB::commit();
            return $post->load(['author', 'reactions.user']);
        } catch (Exception $e) {
            DB::rollBack();

            if (isset($postData['attachment_path'])) {
                Storage::disk('public')->delete($postData['attachment_path']);
            }

            throw $e;
        }
    }

    /**
     * Update an existing post.
     */
    public function updatePost(int $postId, array $data, $attachment = null): TimelinePost
    {
        $post = $this->findPost($postId);
        $user = Auth::user();

        if ($post->user_id !== $user->id) {
            throw new Exception('You can only edit your own posts.', 403);
        }

        DB::beginTransaction();

        try {
            $updateData = [
                'post_type' => $data['post_type'],
                'content' => $data['content'] ?? null,
            ];

            if ($attachment) {
                $fileData = $this->handleFileUpload($attachment, $data['post_type']);

                if ($post->attachment_path) {
                    Storage::disk('public')->delete($post->attachment_path);
                }

                $updateData = array_merge($updateData, $fileData);
            }

            $this->repository->updatePost($post, $updateData);

            DB::commit();
            return $post->fresh()->load(['author', 'reactions.user']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a post.
     */
    public function deletePost(int $postId): bool
    {
        $post = $this->findPost($postId);
        $user = Auth::user();

        if ($post->user_id !== $user->id) {
            throw new Exception('You can only delete your own posts.', 403);
        }

        if ($post->attachment_path) {
            Storage::disk('public')->delete($post->attachment_path);
        }

        return $this->repository->deletePost($post);
    }

    /**
     * Add or toggle reaction on a post.
     */
    public function toggleReaction(int $postId, string $emoji): array
    {
        $this->validateEmoji($emoji);

        $post = $this->findPost($postId);
        $user = Auth::user();

        $reaction = $this->repository->toggleReaction($post, $user, $emoji);

        $post->load('reactions');

        return [
            'removed' => !$reaction->exists,
            'reaction' => $reaction->exists ? [
                'id' => $reaction->id,
                'emoji' => $reaction->emoji,
                'emoji_char' => $reaction->emoji_char,
            ] : null,
            'summary' => $post->reaction_summary,
        ];
    }

    /**
     * Remove reaction from a post.
     */
    public function removeReaction(int $postId): bool
    {
        $post = $this->findPost($postId);
        $user = Auth::user();

        return $this->repository->removeReaction($post, $user);
    }

    /**
     * Add a comment to a post.
     */
    public function addComment(int $postId, string $content): PostComment
    {
        if (empty(trim($content))) {
            throw new Exception('Comment content cannot be empty.', 422);
        }

        if (strlen($content) > 1000) {
            throw new Exception('Comment is too long. Maximum 1000 characters.', 422);
        }

        $post = $this->findPost($postId);
        $user = Auth::user();

        $comment = $this->repository->addComment($post, $user, $this->sanitizeContent($content));

        return $comment->load('author');
    }

    /**
     * Get comments for a post.
     */
    public function getComments(int $postId, int $page = 1, int $perPage = 20): array
    {
        $post = $this->findPost($postId);
        $comments = $this->repository->getPostComments($post, $perPage);

        return [
            'data' => $comments->items(),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'last_page' => $comments->lastPage(),
            ],
        ];
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(int $commentId): bool
    {
        $comment = PostComment::with('post')->find($commentId);

        if (!$comment) {
            throw new Exception('Comment not found.', 404);
        }

        $user = Auth::user();

        if ($comment->user_id !== $user->id) {
            throw new Exception('You can only delete your own comments.', 403);
        }

        return $this->repository->deleteComment($comment);
    }

    /**
     * Handle file upload for post attachments.
     */
    protected function handleFileUpload($file, string $postType): array
    {
        $maxSize = $postType === 'voice_note' ? 5 * 1024 * 1024 : 10 * 1024 * 1024;

        if ($file->getSize() > $maxSize) {
            throw new Exception('File size exceeds maximum allowed.', 422);
        }

        $allowedMimes = $postType === 'voice_note'
            ? ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/m4a', 'audio/aac']
            : ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new Exception('Invalid file type.', 422);
        }

        $path = $file->store('timeline/' . date('Y/m'), 'public');

        return [
            'attachment_path' => $path,
            'attachment_mime_type' => $file->getMimeType(),
            'attachment_size_bytes' => $file->getSize(),
        ];
    }

    /**
     * Validate emoji type.
     */
    protected function validateEmoji(string $emoji): void
    {
        $validEmojis = ['heart', 'laugh', 'wow', 'sad', 'angry'];

        if (!in_array($emoji, $validEmojis)) {
            throw new Exception('Invalid emoji type.', 422);
        }
    }

    /**
     * Sanitize content to prevent XSS.
     */
    protected function sanitizeContent(string $content): string
    {
        return e(strip_tags($content));
    }

    /**
     * Verify user has access to the post.
     */
    protected function verifyPostAccess(TimelinePost $post): void
    {
        $user = Auth::user();

        if (!$user->couple_id || $post->couple_id !== $user->couple_id) {
            throw new Exception('You do not have access to this post.', 403);
        }
    }
}
