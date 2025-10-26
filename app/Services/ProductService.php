<?php

namespace App\Services;

use App\Jobs\GenerateProductImageThumbnails;
use App\Jobs\ProcessProductImages;
use App\Models\Product;
use App\Models\ProductCustomFieldValue;
use App\Models\ProductImage;
use App\Models\User;
use App\Repositories\RecentSearchRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(private readonly ProductRepository $productRepository, private readonly RecentSearchRepository $recentSearchRepository, private readonly RekognitionService $rekognitionService)
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

        $customFields = $data['custom_fields'] ?? [];

        if (!empty($data['images'])) {
            $this->saveProductImages($product, $data['images']);
        }

        if (count($customFields) > 0) {
            $this->attachCustomFields($product, $customFields);
        }

        return $product;
    }

    public function updateProduct($product, $data)
    {
        $data = $this->handleImagesAndStatus($product, $data);

        $customFields = $data['custom_fields'] ?? [];

        if (count($customFields) > 0) {
            $this->attachCustomFields($product, $customFields);
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

        if (isset($data['name']) && !empty($data['name'])) {
            $name = $data['name'];
            if (strlen($name) < 4) {
                $query = $query->where('name', 'LIKE', "$name%");
            } else {
                $query = $query->whereRaw(
                    "MATCH(name) AGAINST (? IN BOOLEAN MODE)",
                    [$name . '*']
                );
            }
        }

        if (!is_null($price_order)) {
            $query = $query->orderBy('price', $price_order);
        }

        if (isset($data['category']) && !empty($data['category'])) {
            $categoryId = $data['category'];
            $query->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', '=', $categoryId);
            });
        };

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

    // protected function processProductImages(Product $product, array $images): void
    // {
    //     foreach ($images as $image) {
    //         $tempPath = $image->store('products', 'public');
    //         ProcessProductImages::dispatch($tempPath, $product);
    //     }
    // }

    protected function saveProductImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            $path = $image->store('products', 'public');

            $productImage = ProductImage::create([
                'image_url' => config('app.url') . Storage::url($path),
                'product_id' => $product->id,
            ]);
        }

        GenerateProductImageThumbnails::dispatch($productImage)->onConnection('database');
    }

    protected function attachCustomFields(Product $product, array $customFields): void
    {
        $customFieldValues = [];

        foreach ($customFields as $fieldId => $value) {
            $customFieldValues[] = [
                'product_id' => $product->id,
                'custom_field_id' => $fieldId,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ProductCustomFieldValue::upsert(
            $customFieldValues,
            ['product_id', 'custom_field_id'],
            ['value', 'updated_at']
        );
    }

    private function handleImagesAndStatus(Product $product, array $data): array
    {
        $newImagesUploaded = false;
        $categoryChanged = false;

        if (!empty($data['category_ids'])) {
            $oldCategoryIds = $product->categories()->pluck('categories.id')->toArray();
            $newCategoryIds = $data['category_ids'];

            $categoryChanged = count(array_diff($newCategoryIds, $oldCategoryIds)) > 0
                || count(array_diff($oldCategoryIds, $newCategoryIds)) > 0;
        }

        if (!empty($data['images'])) {
            $this->saveProductImages($product, $data['images']);
            $newImagesUploaded = true;
        }

        if ($categoryChanged) {
            $product->categories()->sync($newCategoryIds);
            $product->images()->update(['scanned' => false]);
        }

        if ($newImagesUploaded || $categoryChanged) {
            $data['status'] = Product::STATUS_PENDING;
        }

        return $data;
    }
}
