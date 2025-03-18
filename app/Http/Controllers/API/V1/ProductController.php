<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function __construct(private readonly ProductService $productService)
    {}

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $products = $this->productService->getAll($limit);
        
        return $this->success(ProductResource::collection($products));
    }
        public function create(CreateProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());
        
        return $this->success(new ProductResource($product));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product = $this->productService->updateProduct($product , $request->validated());

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

        return $this->success(new ProductResource($product) , 'Categories attached successfully');
    }

    public function detachCategories(UpdateProductRequest $request, Product $product)
    {
        $this->productService->detachCategoriesFromProduct($product, $request->category_ids);

        return $this->success(new ProductResource($product) , 'Categories deattached successfully');
    }
}
