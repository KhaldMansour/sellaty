<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use App\Models\Category;
use App\Models\CustomField;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

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
        if (isset($data['image'])) {
            $imagePath = str_replace([url('/storage/'), 'storage/'], '', $category->image_url);
            Storage::disk('public')->delete($imagePath);

            $imagePath = request()->file('image')->store('categories', 'public');
            $imageUrl = asset('storage/' . $imagePath);
            $data['image_url'] = $imageUrl;
        }

        return $this->categoryRepository->update($data, $category->id);
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

    public function getProducts(Category $category, $limit = 10)
    {
        $categoryProducts = $category->products()
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $categoryProducts;
    }

    public function getNames()
    {
        return Category::select('id', 'name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->getTranslation('name', app()->getLocale()),
                    'custom_fields' => $category->customFields
                ];
            });
    }

    public function addCustomField(Category $category, array $data): CustomField
    {
        return $category->customFields()->create($data);
    }
}
