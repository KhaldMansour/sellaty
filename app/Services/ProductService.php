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
        $this->saveSearchValue($data, auth()->user());

        return $this->productRepository->with('categories')->orderBy('created_at', 'desc')->paginate($limit);
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
        if (is_null($user) || empty($data['searchFields']) || empty($data['search'])) {
            return;
        }

        $fields = explode(';', $data['searchFields']);

        $allowedFields = ['name', 'description'];

        foreach ($fields as $field) {
            [$fieldName] = explode(':', $field);

            if (in_array(trim($fieldName), $allowedFields)) {
                $this->recentSearchRepository->save(
                    $user->id,
                    $fieldName,
                    $data['search'],
                    new Product()
                );
            }
        }
    }

    public function getSellerProducts(User $user, $limit = 10)
    {
        $products = $this->productRepository->where('user_id', $user->id)->paginate($limit);
     
        return $products;
    }

    public function getSellerActiveProducts(User $user, $limit = 10)
    {
        $products = $this->productRepository->where('user_id', $user->id)->where('status', Product::STATUS_ACTIVE)->paginate($limit);
     
        return $products;
    }
}
