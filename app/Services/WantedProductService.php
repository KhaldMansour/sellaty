<?php

namespace App\Services;

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
        if (empty($data['search']) || empty($data['searchFields'] || empty($user))) {
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
                    new WantedProduct()
                );
            }
        }
    }
}
