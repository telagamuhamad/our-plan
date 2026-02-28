<?php

namespace App\Services;

use App\Models\SavingCategory;

class SavingCategoryService
{
    /**
     * Get all categories ordered
     */
    public function getAllCategories()
    {
        return SavingCategory::ordered()->get();
    }

    /**
     * Get default categories
     */
    public function getDefaultCategories()
    {
        return SavingCategory::default()->ordered()->get();
    }

    /**
     * Get custom categories
     */
    public function getCustomCategories()
    {
        return SavingCategory::custom()->ordered()->get();
    }

    /**
     * Find category by ID
     */
    public function findCategory($id)
    {
        return SavingCategory::find($id);
    }

    /**
     * Create new category
     */
    public function createCategory(array $payload)
    {
        return SavingCategory::create($payload);
    }

    /**
     * Update category
     */
    public function updateCategory($id, array $payload)
    {
        $category = SavingCategory::find($id);
        if (!$category) {
            return null;
        }
        $category->update($payload);
        return $category->fresh();
    }

    /**
     * Delete category
     */
    public function deleteCategory($id)
    {
        $category = SavingCategory::find($id);
        if (!$category) {
            return false;
        }

        // Don't allow deleting default categories
        if ($category->is_default) {
            return false;
        }

        return $category->delete();
    }

    /**
     * Get categories grouped (default and custom)
     */
    public function getGroupedCategories()
    {
        return [
            'default' => $this->getDefaultCategories(),
            'custom' => $this->getCustomCategories(),
        ];
    }
}
