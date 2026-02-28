<?php

namespace Database\Seeders;

use App\Models\SavingCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SavingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Emergency Fund',
                'icon' => '🚨',
                'color' => '#dc3545',
                'description' => 'Dana darurat untuk kebutuhan mendesak',
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Vacation',
                'icon' => '✈️',
                'color' => '#0dcaf0',
                'description' => 'Tabungan liburan dan traveling',
                'is_default' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Gadget',
                'icon' => '📱',
                'color' => '#6f42c1',
                'description' => 'Tabungan beli gadget baru',
                'is_default' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Investment',
                'icon' => '📈',
                'color' => '#198754',
                'description' => 'Tabungan untuk investasi',
                'is_default' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Vehicle',
                'icon' => '🚗',
                'color' => '#fd7e14',
                'description' => 'Tabungan beli kendaraan',
                'is_default' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Home',
                'icon' => '🏠',
                'color' => '#d63384',
                'description' => 'Tabungan untuk rumah atau renovasi',
                'is_default' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Education',
                'icon' => '📚',
                'color' => '#0d6efd',
                'description' => 'Tabungan pendidikan dan kursus',
                'is_default' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Wedding',
                'icon' => '💍',
                'color' => '#e83e8c',
                'description' => 'Tabungan pernikahan',
                'is_default' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Health',
                'icon' => '🏥',
                'color' => '#20c997',
                'description' => 'Tabungan kesehatan dan checkup',
                'is_default' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Shopping',
                'icon' => '🛍️',
                'color' => '#6610f2',
                'description' => 'Tabungan belanja keinginan',
                'is_default' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Gift',
                'icon' => '🎁',
                'color' => '#ffc107',
                'description' => 'Tabungan untuk hadiah',
                'is_default' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Other',
                'icon' => '💰',
                'color' => '#6c757d',
                'description' => 'Tabungan tujuan lainnya',
                'is_default' => true,
                'sort_order' => 12,
            ],
        ];

        foreach ($categories as $category) {
            SavingCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
