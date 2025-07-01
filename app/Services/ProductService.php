<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Repositories\RecentSearchRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(private readonly ProductRepository $productRepository, private readonly RecentSearchRepository $recentSearchRepository)
    {
    }

    public function getAll(int $limit = 10, $data = null)
    {
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;

        $this->saveSearchValue($data, auth()->user());

        return $this->productRepository->with('categories')
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    public function createProduct($data)
    {
        $user = auth()->user();
        $data['user_id'] = $user->id;
        $product = $this->productRepository->create($data);
        $product->categories()->attach($data['category_ids']);

        return $product;
    }

    public function updateProduct($product, $data)
    {
        if (isset($data['image'])) {
            $imagePath = str_replace([url('/storage/'), 'storage/'], '', $product->image_url);
            Storage::disk('public')->delete($imagePath);

            $imagePath = request()->file('image')->store('products', 'public');
            $imageUrl = asset('storage/' . $imagePath);
            $data['image_url'] = $imageUrl;
        }

        return $this->productRepository->update($data, $product->id);
    }

    public function getProductById($id)
    {
        return $this->productRepository->find($id);
    }

    public function toggleFeaturedStatus($product)
    {
        $product->featured = !$product->featured;

        return $this->productRepository->update(['featured' => $product->featured], $product->id);
    }

    public function attachCategoriesToProduct($product, $categoryIds)
    {
        $product->categories()->syncWithoutDetaching($categoryIds);
    }

    public function detachCategoriesFromProduct($product, $categoryIds)
    {
        $product->categories()->detach($categoryIds);
    }

    private function saveSearchValue($data, $user)
    {
        if (is_null($user) || empty($data['search'])) {
            return;
        }

        $searchQuery = $data['search'];
        $allowedFields = ['name', 'description'];

        $conditions = explode(';', $searchQuery);

        foreach ($conditions as $condition) {
            if (strpos($condition, ':') !== false) {
                [$fieldName, $searchValue] = explode(':', $condition, 2);

                $fieldName = trim($fieldName);
                $searchValue = trim($searchValue);

                if (in_array($fieldName, $allowedFields) && !empty($searchValue)) {
                    $this->recentSearchRepository->save(
                        $user->id,
                        $fieldName,
                        $searchValue,
                        new Product()
                    );
                }
            }
        }
    }

    public function filterProducts(int $limit = 10, $data = null)
    {
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;

        $this->saveSearchValue($data, auth()->user());

        $creation_order = isset($data['creation_order']) ? $data['creation_order'] : 'desc';

        $price_order = isset($data['price_order']) && !empty($data['price_order']) ? $data['price_order'] : null;

        $query = $this->productRepository->with('categories')
            ->where('status', Product::STATUS_ACTIVE);

        if (!is_null($price_order)) {
            $query = $query->orderBy('price', $price_order);
        }

        $query = $query->orderBy('created_at', $creation_order);

        return $query->paginate($limit);
    }

    public function getSellerProducts(User $user, $limit = 10)
    {
        $products = $this->productRepository->where('user_id', $user->id)
            ->where('status', Product::STATUS_ACTIVE)
            ->paginate($limit);

        return $products;
    }

    public function getSellerActiveProducts(User $user, $limit = 10)
    {
        $products = $this->productRepository->where('user_id', $user->id)->where('status', Product::STATUS_ACTIVE)->paginate($limit);

        return $products;
    }
}
