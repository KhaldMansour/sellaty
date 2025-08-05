<?php

namespace App\Services;

use App\Models\User;
use App\Models\WantedProduct;
use App\Repositories\RecentSearchRepository;
use App\Repositories\WantedProductRepository;

class WantedProductService
{
    public function __construct(private readonly WantedProductRepository $wantedProductRepository, private readonly RecentSearchRepository $recentSearchRepository)
    {
    }

    public function getAll(int $limit, $data = null)
    {
        $this->saveSearchValue($data, auth()->user());

        return $this->wantedProductRepository->paginate($limit);
    }

    public function show(int $id)
    {
    }

    public function create(array $data)
    {
        try {
            $user = auth()->user();
            $data['user_id'] = $user->id;
            $wantedProduct = $this->wantedProductRepository->create($data);
            $wantedProduct->categories()->attach($data['category_ids']);

            return $wantedProduct;
        } catch (\Exception $e) {
            throw new \Exception('Error while creating the wanted product: ' . $e->getMessage());
        }
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
}
