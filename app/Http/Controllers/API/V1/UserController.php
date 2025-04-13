<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\WantedProductResource;
use App\Services\WantedProductService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly WantedProductService $wantedProductService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/profile/wanted-products",
     *     summary="Get a list of the authenticated user's wanted products",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of items to return per page",
     *         @OA\Schema(
     *             type="integer",
     *             default=10,
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             default=1,
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of the user's wanted products",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/WantedProductSchema")
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - No valid token provided",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Unauthorized"
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function myWantedProducts(Request $request)
    {
        $user = auth()->user();
        $limit = $request->input('limit', 10);

        return $this->success(WantedProductResource::collection($user->wantedProducts()->paginate($limit)));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/profile/products",
     *     summary="Get a list of the authenticated user's products",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of items to return per page",
     *         @OA\Schema(
     *             type="integer",
     *             default=10,
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             default=1,
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of the user's products",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ProductSchema")
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - No valid token provided",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Unauthorized"
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function myProducts(Request $request)
    {
        $user = auth()->user();
        $limit = $request->input('limit', 10);

        return $this->success(ProductResource::collection($user->products()->paginate($limit)));
    }
}
