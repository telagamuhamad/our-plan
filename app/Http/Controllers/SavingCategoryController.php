<?php

namespace App\Http\Controllers;

use App\Services\SavingCategoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavingCategoryController extends Controller
{
    protected $service;

    public function __construct(SavingCategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all categories (API/JSON)
     */
    public function index()
    {
        $categories = $this->service->getAllCategories();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $payload = [
                'name' => $request->name,
                'icon' => $request->icon ?? '💰',
                'color' => $request->color ?? '#6c757d',
                'description' => $request->description,
                'is_default' => false,
                'sort_order' => $request->sort_order ?? 999,
            ];

            $category = $this->service->createCategory($payload);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'category' => $category
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        $category = $this->service->findCategory($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Don't allow editing default categories
        if ($category->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit default categories'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $payload = [
                'name' => $request->name,
                'icon' => $request->icon ?? $category->icon,
                'color' => $request->color ?? $category->color,
                'description' => $request->description,
            ];

            $category = $this->service->updateCategory($id, $payload);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'category' => $category
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        $category = $this->service->findCategory($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $result = $this->service->deleteCategory($id);
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete default categories'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
