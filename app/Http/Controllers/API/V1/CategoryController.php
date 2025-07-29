<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\StoreCustomFieldRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CustomFieldResource;
use App\Http\Resources\ProductResource;
use App\Services\CategoryService;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     summary="Get a list of categories with optional filters.",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="filters",
     *         in="query",
     *         description="Query parameters for filtering the categories.",
     *         required=false,
     *         @OA\Schema(ref="#/components/schemas/ListResourceRequestSchema")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of categories.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Categories fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/CategorySchema")
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
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $categories = $this->categoryService->getAll($limit);

        return $this->success(CategoryResource::collection($categories));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     summary="Create a new category",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 ref="#/components/schemas/CreateCategoryRequestSchema"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category created successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Category created successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/CategorySchema"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity - Validation errors in request data.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", additionalProperties={
     *                 @OA\Property(property="name", type="array", items=@OA\Items(type="string"))
     *             })
     *         )
     *     )
     * )
     */
    public function create(CreateCategoryRequest $request)
    {
        $category = $this->categoryService->create($request->validated());

        return $this->success(new CategoryResource($category));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories/update/{id}",
     *     summary="Update an existing category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="The ID of the category to update"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 ref="#/components/schemas/UpdateCategoryRequestSchema"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Category updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/CategorySchema"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     )
     * )
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category = $this->categoryService->update($category, $request->validated());

        return $this->success(new CategoryResource($category));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{id}",
     *     summary="Get a specific category by ID",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="The ID of the category to retrieve"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Category retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/CategorySchema"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     )
     * )
     */
    public function show(Category $category)
    {
        return $this->success(new CategoryResource($category));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/categories/{id}",
     *     summary="Delete a specific category by ID",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="The ID of the category to delete"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Category deleted successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/CategorySchema"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     )
     * )
     */
    public function destroy(Category $category)
    {
        $this->categoryService->delete($category);

        return $this->success(new CategoryResource($category), 'Category deleted successfully');
        ;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{id}/products/stock",
     *     summary="Get the stock of products in a specific category.",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="The ID of the category"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The stock of products in the category.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="stock", type="integer", example=4),
     *                 @OA\Property(property="category", ref="#/components/schemas/CategorySchema")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     )
     * )
     */

    public function countStockByCategory(Category $category)
    {
        $stockData = $this->categoryService->countStockByCategory($category);

        return $this->success(['stock' => $stockData , 'category' => new CategoryResource($category)]);
    }

    public function popularCategories()
    {
        $popularCategories = Category::withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->take(5)
            ->get();

        return $this->success(CategoryResource::collection($popularCategories));
    }

    public function getNames()
    {
        $categoriesNames = $this->categoryService->getNames();

        return $this->success($categoriesNames);
    }

    public function getProducts(Category $category)
    {
        $limit = request()->input('limit', 10);

        $categoryProducts = $this->categoryService->getProducts($category, $limit);

        return $this->success(['category' => new CategoryResource($category), 'products' => ProductResource::collection($categoryProducts)]);
    }

    public function addCustomField(Category $category, StoreCustomFieldRequest $request)
    {
        $customField = $this->categoryService->addCustomField($category, $request->validated());

        return $this->success(new CustomFieldResource($customField), 'Custom field added successfully');
    }
}
