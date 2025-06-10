<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\ListResourceRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Get a list of products.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="filters",
     *         in="query",
     *         description="Query parameters for filtering the products.",
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
     *                 @OA\Items(ref="#/components/schemas/ProductSchema")
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

        $products = $this->productService->getAll($limit, $request->validated());

        return $this->success(ProductResource::collection($products));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{product}",
     *     summary="Get a single product by ID",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID of the product to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product fetched successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProductSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     )
     * )
     */
    public function show(Product $product)
    {
        return $this->success(new ProductResource($product), 'Product fetched successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Create a new product",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/CreateProductRequestSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The created product",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/ProductSchema")
     *         )
     *     )
     * )
     */
    public function create(CreateProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());

        return $this->success(new ProductResource($product));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product = $this->productService->updateProduct($product, $request->validated());

        return $this->success(new ProductResource($product));
    }

    public function toggleFeatured(Product $product)
    {
        $product = $this->productService->toggleFeaturedStatus($product);

        return $this->success(new ProductResource($product));
    }

    public function attachCategories(UpdateProductRequest $request, Product $product)
    {
        $this->productService->attachCategoriesToProduct($product, $request->category_ids);

        return $this->success(new ProductResource($product), 'Categories attached successfully');
    }

    public function detachCategories(UpdateProductRequest $request, Product $product)
    {
        $this->productService->detachCategoriesFromProduct($product, $request->category_ids);

        return $this->success(new ProductResource($product), 'Categories deattached successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{user}/products",
     *     summary="Get Active products by seller",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user (seller)",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit the number of products returned",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products for the given seller",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Seller products fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ProductSchema")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     )
     * )
     */
    public function sellerActiveProducts(User $user, Request $request)
    {
        $limit = $request->input('limit', 10);

        $products = $this->productService->getSellerActiveProducts($user, $limit);

        return $this->success(ProductResource::collection($products));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products/filter",
     *     summary="Get a list of products with optional filters.",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Create a new product",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/FilterProductRequest")
     *         )
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
     *                 @OA\Items(ref="#/components/schemas/ProductSchema")
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
    public function filter(ListResourceRequest $request)
    {
        $limit = $request->input('limit', 10);

        $products = $this->productService->getAll($limit, $request->validated());

        return $this->success(ProductResource::collection($products));
    }
}
