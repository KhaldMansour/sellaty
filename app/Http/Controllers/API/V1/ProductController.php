<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\ListResourceRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Get a list of products with optional filters.",
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
     *                 @OA\Items(ref="#/components/schemas/ProductResource")
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
        $products = $this->productService->getAll($limit);

        return $this->success(ProductResource::collection($products));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     summary="Create a new product",
     *     operationId="createProduct",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "price", "quantity", "category_ids[]", "images[]", "featured"},
     *                 @OA\Property(property="name", type="string", maxLength=255, description="Product name", example="Product Name"),
     *                 @OA\Property(property="description", type="string", description="Product description", example="Product description goes here."),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     items=@OA\Items(type="string", format="binary"),
     *                     description="Product images (array of files)"
     *                 ),
     *                 @OA\Property(
     *                     property="category_ids[]",
     *                     type="array",
     *                     items=@OA\Items(type="integer"),
     *                     description="List of category IDs"
     *                 ),
     *                 @OA\Property(property="price", type="number", format="float", description="Product price", example=29.99),
     *                 @OA\Property(property="quantity", type="integer", description="Product quantity", example=100),
     *                 @OA\Property(
     *                     property="featured",
     *                     type="integer",
     *                     enum={0, 1},
     *                     description="Is the product featured (0 = false, 1 = true)",
     *                     example=1
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Product Name"),
     *             @OA\Property(property="price", type="number", format="float", example=29.99),
     *             @OA\Property(property="quantity", type="integer", example=100),
     *             @OA\Property(
     *                 property="category_ids",
     *                 type="array",
     *                 items=@OA\Items(type="integer"),
     *                 description="List of category IDs"
     *             ),
     *             @OA\Property(
     *                 property="featured",
     *                 type="integer",
     *                 enum={0, 1},
     *                 description="Is the product featured (0 = false, 1 = true)",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="images",
     *                 type="array",
     *                 items=@OA\Items(type="string", format="binary"),
     *                 description="Product images (array of files)"
     *             )
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
}
