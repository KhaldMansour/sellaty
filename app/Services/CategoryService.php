<?php
// app/Services/CategoryService.php

namespace App\Services;

use App\Repositories\CategoryRepository;
use App\Models\Category;

class CategoryService
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {}

    public function getAll(int $limit = 10)
    {
        return $this->categoryRepository->paginate($limit);
    }

    public function create(array $data)
    {
        return $this->categoryRepository->create($data);
    }

    public function update(Category $category, array $data)
    {
        $category->update($data);

        return $category;
    }

    public function getById($categoryId)
    {
        return $this->categoryRepository->find($categoryId);
    }

    public function delete(Category $category)
    {
        $category->delete();
    }

    public function countStockByCategory(Category $category)
    {
        $totalStock = $category->products()->sum('quantity');

        return $totalStock;
    }
}