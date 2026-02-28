<?php

namespace App\Repositories;

use App\Models\Couple;
use App\Models\PostComment;
use App\Models\PostReaction;
use App\Models\TimelinePost;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TimelinePostRepository
{
    protected TimelinePost $model;

    public function __construct(TimelinePost $model)
    {
        $this->model = $model;
    }

    /**
     * Get posts for a couple with pagination.
     */
    public function getCouplePosts(Couple $couple, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->forCouple($couple->id)
            ->with(['author', 'reactions.user', 'comments.author'])
            ->orderBy('posted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a post by ID with relationships.
     */
    public function findPostWithRelations(int $postId): ?TimelinePost
    {
        return $this->model
            ->with(['author', 'reactions.user', 'comments.author'])
            ->find($postId);
    }

    /**
     * Create a new post.
     */
    public function createPost(Couple $couple, User $author, array $data): TimelinePost
    {
        return $this->model->create(array_merge($data, [
            'couple_id' => $couple->id,
            'user_id' => $author->id,
            'posted_at' => now(),
        ]));
    }

    /**
     * Update an existing post.
     */
    public function updatePost(TimelinePost $post, array $data): bool
    {
        return $post->update($data);
    }

    /**
     * Delete a post.
     */
    public function deletePost(TimelinePost $post): bool
    {
        return $post->delete();
    }

    /**
     * Add or update a reaction.
     * Returns array with 'action' => 'added', 'updated', 'removed' and 'reaction' model
     */
    public function toggleReaction(
        TimelinePost $post,
        User $user,
        string $emoji
    ): array {
        $existingReaction = $post->reactions()->where('user_id', $user->id)->first();

        if ($existingReaction) {
            if ($existingReaction->emoji === $emoji) {
                // Remove reaction if same emoji
                $existingReaction->delete();
                return ['action' => 'removed', 'reaction' => $existingReaction];
            }
            // Update reaction if different emoji
            $existingReaction->update(['emoji' => $emoji]);
            return ['action' => 'updated', 'reaction' => $existingReaction->fresh()];
        }

        // Create new reaction
        $newReaction = $post->reactions()->create([
            'user_id' => $user->id,
            'emoji' => $emoji,
        ]);
        return ['action' => 'added', 'reaction' => $newReaction];
    }

    /**
     * Remove a reaction.
     */
    public function removeReaction(TimelinePost $post, User $user): bool
    {
        return $post->reactions()->where('user_id', $user->id)->delete() > 0;
    }

    /**
     * Add a comment to a post.
     */
    public function addComment(
        TimelinePost $post,
        User $user,
        string $content
    ): PostComment {
        return $post->comments()->create([
            'user_id' => $user->id,
            'content' => $content,
            'commented_at' => now(),
        ]);
    }

    /**
     * Get comments for a post with pagination.
     */
    public function getPostComments(TimelinePost $post, int $perPage = 20): LengthAwarePaginator
    {
        return $post->comments()
            ->with('author')
            ->orderBy('commented_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(PostComment $comment): bool
    {
        return $comment->delete();
    }

    /**
     * Check if post belongs to couple.
     */
    public function postBelongsToCouple(TimelinePost $post, int $coupleId): bool
    {
        return $post->couple_id === $coupleId;
    }
}
