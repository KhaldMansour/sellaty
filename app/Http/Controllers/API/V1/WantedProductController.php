<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateWantedProductRequest;
use App\Http\Requests\ListResourceRequest;
use App\Http\Resources\WantedProductResource;
use App\Models\User;
use App\Models\WantedProduct;
use App\Services\WantedProductService;
use Illuminate\Http\Request;

class WantedProductController extends Controller
{
    public function __construct(private readonly WantedProductService $wantedProductService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/wanted-products",
     *     summary="Get a list of products with optional filters.",
     *     tags={"Wanted Products"},
     *     @OA\Parameter(
     *         name="filters",
     *         in="query",
     *         description="Query parameters for filtering the wanted products.",
     *         required=false,
     *         @OA\Schema(ref="#/components/schemas/ListResourceRequestSchema")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of products.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Products fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/WantedProductSchema")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     )
     * )
     */
    public function index(ListResourceRequest $request)
    {
        $limit = $request->input('limit', 10);
        $wantedProducts = $this->wantedProductService->getAll($limit, $request->validated());

        return $this->success(WantedProductResource::collection($wantedProducts));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/wanted-products",
     *     summary="Create a new wanted product",
     *     tags={"Wanted Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Create a new wanted product",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/CreateWantedProductRequestSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The created wanted product",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/WantedProductSchema")
     *         )
     *     )
     * )
     */
    public function create(CreateWantedProductRequest $request)
    {
        $wantedProduct = $this->wantedProductService->create($request->validated());

        return $this->success(new WantedProductResource($wantedProduct));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/wanted-products/{id}",
     *     summary="Get the details of a wanted product",
     *     tags={"Wanted Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the wanted product to retrieve",
     *         @OA\Schema(
     *             type="integer"
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The wanted product details",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/WantedProductSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="The wanted product was not found",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Wanted product not found"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function show(WantedProduct $wantedProduct)
    {
        return $this->success(new WantedProductResource($wantedProduct));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{user}/wanted-products",
     *     summary="Get active wanted products for a specific buyer",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the buyer user",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of results to return per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active wanted products for the buyer",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Wanted products fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/WantedProductSchema")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Buyer user not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function buyerActiveWantedProducts(User $user, Request $request)
    {
        $limit = $request->input('limit', 10);

        $wantedProducts = $this->wantedProductService->getBuyerActiveWantedProducts($user, $limit);

        return $this->success(WantedProductResource::collection($wantedProducts));
    }
}
