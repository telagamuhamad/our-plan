<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddCommentRequest;
use App\Http\Requests\AddReactionRequest;
use App\Http\Requests\CreatePostRequest;
use App\Http\Resources\PostCommentResource;
use App\Http\Resources\TimelinePostResource;
use App\Services\Api\TimelineService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimelineApiController extends Controller
{
    protected \App\Services\Api\TimelineService $service;

    public function __construct(\App\Services\Api\TimelineService $service)
    {
        $this->service = $service;
    }

    /**
     * Get timeline feed.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 15);

            $result = $this->service->getFeed($page, $perPage);

            return response()->json([
                'success' => true,
                'data' => TimelinePostResource::collection($result['data']),
                'pagination' => $result['pagination'],
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Get a single post.
     */
    public function show($postId): JsonResponse
    {
        try {
            $post = $this->service->findPost($postId);

            return response()->json([
                'success' => true,
                'data' => TimelinePostResource::make($post),
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Create a new post.
     */
    public function store(CreatePostRequest $request): JsonResponse
    {
        try {
            $attachment = $request->file('attachment');

            $data = [
                'post_type' => $request->post_type,
                'content' => $request->content,
            ];

            $post = $this->service->createPost($data, $attachment);

            return response()->json([
                'success' => true,
                'message' => 'Postingan berhasil dibuat.',
                'data' => TimelinePostResource::make($post),
            ], 201);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Update a post.
     */
    public function update(CreatePostRequest $request, $postId): JsonResponse
    {
        try {
            $attachment = $request->file('attachment');

            $data = [
                'post_type' => $request->post_type,
                'content' => $request->content,
            ];

            $post = $this->service->updatePost($postId, $data, $attachment);

            return response()->json([
                'success' => true,
                'message' => 'Postingan berhasil diperbarui.',
                'data' => TimelinePostResource::make($post),
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Delete a post.
     */
    public function destroy($postId): JsonResponse
    {
        try {
            $this->service->deletePost($postId);

            return response()->json([
                'success' => true,
                'message' => 'Postingan berhasil dihapus.',
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Add or toggle reaction on a post.
     */
    public function react(AddReactionRequest $request, $postId): JsonResponse
    {
        try {
            $result = $this->service->toggleReaction(
                $postId,
                $request->emoji
            );

            return response()->json([
                'success' => true,
                'message' => $result['removed'] ? 'Reaksi dihapus.' : 'Reaksi berhasil ditambahkan.',
                'data' => $result,
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Remove reaction from a post.
     */
    public function unreact($postId): JsonResponse
    {
        try {
            $this->service->removeReaction($postId);

            return response()->json([
                'success' => true,
                'message' => 'Reaksi berhasil dihapus.',
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Add a comment to a post.
     */
    public function comment(AddCommentRequest $request, $postId): JsonResponse
    {
        try {
            $comment = $this->service->addComment(
                $postId,
                $request->content
            );

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil ditambahkan.',
                'data' => PostCommentResource::make($comment),
            ], 201);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Get comments for a post.
     */
    public function comments(Request $request, $postId): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);

            $result = $this->service->getComments($postId, $page, $perPage);

            return response()->json([
                'success' => true,
                'data' => PostCommentResource::collection($result['data']),
                'pagination' => $result['pagination'],
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Delete a comment.
     */
    public function deleteComment($commentId): JsonResponse
    {
        try {
            $this->service->deleteComment($commentId);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus.',
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Format error response.
     */
    protected function errorResponse(Exception $e): JsonResponse
    {
        $statusCode = $e->getCode() >= 400 && $e->getCode() < 600
            ? $e->getCode()
            : 500;

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], $statusCode);
    }
}
