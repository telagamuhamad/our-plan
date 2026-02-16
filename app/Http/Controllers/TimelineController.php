<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddCommentRequest;
use App\Http\Requests\AddReactionRequest;
use App\Http\Requests\CreatePostRequest;
use App\Services\TimelineService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimelineController extends Controller
{
    protected TimelineService $service;

    public function __construct(TimelineService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the timeline feed.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $posts = $this->service->getFeed($perPage);

            return view('timeline.index', [
                'posts' => $posts,
                'user' => Auth::user(),
            ]);
        } catch (Exception $e) {
            return redirect()->route('pairing.status')
                ->with('error', 'Anda harus terhubung dengan pasangan untuk melihat timeline.');
        }
    }

    /**
     * Show the create post form.
     */
    public function create()
    {
        return view('timeline.create');
    }

    /**
     * Store a new post.
     */
    public function store(CreatePostRequest $request)
    {
        try {
            $attachment = $request->file('attachment');

            $data = [
                'post_type' => $request->post_type,
                'content' => $request->content,
            ];

            $this->service->createPost($data, $attachment);

            return redirect()->route('timeline.index')
                ->with('success', 'Postingan berhasil dibuat!');
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat postingan: ' . $e->getMessage());
        }
    }

    /**
     * Display a single post.
     */
    public function show(Request $request, $postId)
    {
        try {
            $post = $this->service->findPost($postId);

            // Mark notification as read if accessed from notification
            if ($request->has('notification_id')) {
                $notification = Auth::user()->notifications()->find($request->input('notification_id'));
                if ($notification && !$notification->is_read) {
                    $notification->markAsRead();
                }
            }

            return view('timeline.show', [
                'post' => $post,
            ]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the edit post form.
     */
    public function edit($postId)
    {
        try {
            $post = $this->service->findPost($postId);

            if ($post->user_id !== Auth::id()) {
                return back()->with('error', 'Anda hanya bisa mengedit postingan sendiri.');
            }

            return view('timeline.edit', [
                'post' => $post,
            ]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update a post.
     */
    public function update(CreatePostRequest $request, $postId)
    {
        try {
            $attachment = $request->file('attachment');

            $data = [
                'post_type' => $request->post_type,
                'content' => $request->content,
            ];

            $this->service->updatePost($postId, $data, $attachment);

            return redirect()->route('timeline.index')
                ->with('success', 'Postingan berhasil diperbarui!');
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui postingan: ' . $e->getMessage());
        }
    }

    /**
     * Delete a post.
     */
    public function destroy($postId)
    {
        try {
            $this->service->deletePost($postId);

            return back()->with('success', 'Postingan berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Add or toggle reaction on a post.
     */
    public function react(AddReactionRequest $request, $postId)
    {
        try {
            $result = $this->service->toggleReaction(
                $postId,
                $request->emoji
            );

            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['removed'] ? 'Reaksi dihapus.' : 'Reaksi berhasil ditambahkan.',
                    'removed' => $result['removed'],
                    'emoji' => $request->emoji,
                ]);
            }

            return back()->with('success', 'Reaksi berhasil ditambahkan.');
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove reaction from a post.
     */
    public function unreact($postId)
    {
        try {
            $this->service->removeReaction($postId);

            return back()->with('success', 'Reaksi berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Add a comment to a post.
     */
    public function comment(AddCommentRequest $request, $postId)
    {
        try {
            $this->service->addComment(
                $postId,
                $request->content
            );

            return back()->with('success', 'Komentar berhasil ditambahkan.');
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a comment.
     */
    public function deleteComment($commentId)
    {
        try {
            $this->service->deleteComment($commentId);

            return back()->with('success', 'Komentar berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Load more posts (for infinite scroll).
     */
    public function loadMore(Request $request)
    {
        try {
            $posts = $this->service->getFeed($request->get('per_page', 15));

            return response()->json([
                'html' => view('timeline.partials.posts', ['posts' => $posts])->render(),
                'next_page' => $posts->hasMorePages() ? $posts->currentPage() + 1 : null,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
