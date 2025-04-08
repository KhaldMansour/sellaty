<?php

namespace App\Services;

use App\Repositories\WantedProductRepository;

class WantedProductService
{
    public function __construct(private readonly WantedProductRepository $wantedProductRepository)
    {
    }

    public function getAll(int $limit)
    {
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

            return $this->wantedProductRepository->create($data);
        } catch (\Exception $e) {
            throw new \Exception('Error while creating the wanted product: ' . $e->getMessage());
        }
    }
}
