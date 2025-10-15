<?php

namespace App\Services;

use App\Models\User;
use App\Models\WantedProduct;
use App\Models\WantedProductImage;
use App\Repositories\RecentSearchRepository;
use App\Repositories\WantedProductRepository;
use Illuminate\Support\Facades\Storage;

class WantedProductService
{
    public function __construct(private readonly WantedProductRepository $wantedProductRepository, private readonly RecentSearchRepository $recentSearchRepository)
    {
    }

    public function getAll(int $limit, $data = null)
    {
        $this->saveSearchValue($data, auth()->user());

        return $this->wantedProductRepository
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    public function create(array $data)
    {
        $user = auth()->user();
        $data['user_id'] = $user->id;
        $wantedProduct = $this->wantedProductRepository->create($data);
        $wantedProduct->categories()->sync($data['category_ids']);

        if (!empty($data['images'])) {
            $this->saveWantedProductImages($wantedProduct, $data['images']);
        }

        return $wantedProduct;
    }

    public function updateWantedProduct(WantedProduct $wantedProduct, array $data)
    {
        $data = $this->handleImagesAndStatus($wantedProduct, $data);

        return $this->wantedProductRepository->update($data, $wantedProduct->id);
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
                        new WantedProduct()
                    );
                }
            }
        }
    }


    public function getBuyerActiveWantedProducts(User $user, $limit = 10)
    {
        $products = $this->wantedProductRepository
            ->where('user_id', $user->id)
            ->where('status', WantedProduct::STATUS_ACTIVE)
            ->paginate($limit);

        return $products;
    }

    private function handleImagesAndStatus(WantedProduct $wantedProduct, array $data): array
    {
        $newImagesUploaded = false;
        $categoryChanged = false;

        if (!empty($data['category_ids'])) {
            $oldCategoryIds = $wantedProduct->categories()->pluck('categories.id')->toArray();
            $newCategoryIds = $data['category_ids'];

            $categoryChanged = count(array_diff($newCategoryIds, $oldCategoryIds)) > 0
                || count(array_diff($oldCategoryIds, $newCategoryIds)) > 0;
        }

        if (!empty($data['images'])) {
            $this->saveWantedProductImages($wantedProduct, $data['images']);
            $newImagesUploaded = true;
        }

        if ($categoryChanged) {
            $wantedProduct->categories()->attach($newCategoryIds);
            // $wantedProduct->images()->update(['scanned' => false]);
        }

        if ($newImagesUploaded) {
            $data['status'] = WantedProduct::STATUS_PENDING;
        }

        return $data;
    }

    protected function saveWantedProductImages(WantedProduct $wantedProduct, array $images): void
    {
        $rows = [];
        foreach ($images as $image) {
            $path = $image->store('wanted_products', 'public');
            $rows[] = [
                'image_url' => config('app.url') . Storage::url($path),
                'wanted_product_id' => $wantedProduct->id,
            ];
        }
        WantedProductImage::insert($rows);
    }
}
