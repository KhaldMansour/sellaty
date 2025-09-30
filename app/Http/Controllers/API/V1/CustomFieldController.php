<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchCustomFieldOptionRequest;
use App\Http\Resources\ProductResource;
use App\Models\CustomField;
use App\Models\CustomFieldOption;
use App\Models\Product;
use App\Models\ProductCustomFieldValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomFieldController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/custom-fields/{customFieldId}",
     *     summary="Get options for a given custom field",
     *     tags={"Custom Fields"},
     *     @OA\Parameter(
     *         name="customFieldId",
     *         in="path",
     *         required=true,
     *         description="ID of the custom field",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="parent_option", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="value", type="string", example="BMW")
     *             ),
     *             @OA\Property(property="children", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="value", type="string", example="X6")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Custom Field not found")
     * )
     */
    public function optionsByField($customFieldId)
    {
        $customField = CustomField::with('options')->findOrFail($customFieldId);

        $sortedOptions = $customField->options->sortBy(function ($option) {
            return is_null($option->image_url);
        });

        return $this->success([
            'parent_option' => [
                'id' => $customField->id,
                'value' => $customField->name,
                'image_url' => $customField->image_url,
            ],
            'children' => $sortedOptions->map(function ($child) {
                return [
                    'id' => $child->id,
                    'value' => $child->value,
                    'image_url' => $child->image_url,
                ];
            })->values(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/custom-fields/custom-field-options/{customFieldOptionId}",
     *     summary="Get child options for a given custom field option",
     *     tags={"Custom Fields"},
     *     @OA\Parameter(
     *         name="customFieldOptionId",
     *         in="path",
     *         required=true,
     *         description="ID of the custom field option",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="parent_option", type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="value", type="string", example="BMW"),
     *                 @OA\Property(property="product_count", type="integer", example=5),
     *             ),
     *             @OA\Property(property="children", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=6),
     *                     @OA\Property(property="value", type="string", example="X6"),
     *                     @OA\Property(property="product_count", type="integer", example=5),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Custom Field Option not found")
     * )
     */
    public function childrenByOption($customFieldOptionid)
    {
        $option = CustomFieldOption::orderByRaw('image_url IS NULL ASC')->with('children')->findOrFail($customFieldOptionid);
        $children = $option->children;

        $childKeyPairs = $children->map(function ($child) {
            return [
                'custom_field_id' => $child->custom_field_id,
                'value' => $child->value,
            ];
        });

        $allKeyPairs = $childKeyPairs->push([
            'custom_field_id' => $option->custom_field_id,
            'value' => $option->value,
        ])->toArray();

        $productCounts = DB::table('product_custom_field_values')
            ->join('products', 'product_custom_field_values.product_id', '=', 'products.id')
            ->select(
                'product_custom_field_values.custom_field_id',
                'product_custom_field_values.value',
                DB::raw('COUNT(*) as count')
            )
            ->whereNull('products.deleted_at')
            ->where(function ($query) use ($allKeyPairs) {
                foreach ($allKeyPairs as $pair) {
                    $query->orWhere(function ($q) use ($pair) {
                        $q->where('product_custom_field_values.custom_field_id', $pair['custom_field_id'])
                        ->where('product_custom_field_values.value', $pair['value']);
                    });
                }
            })
            ->groupBy('product_custom_field_values.custom_field_id', 'product_custom_field_values.value')
            ->get()
            ->keyBy(function ($item) {
                return $item->custom_field_id . '|' . $item->value;
            });

        $parentKey = $option->custom_field_id . '|' . $option->value;
        $parentCount = $productCounts[$parentKey]->count ?? 0;

        $childrenWithCounts = $children->map(function ($child) use ($productCounts) {
            $key = $child->custom_field_id . '|' . $child->value;

            return [
                'id' => $child->id,
                'value' => $child->value,
                'product_count' => $productCounts[$key]->count ?? 0,
            ];
        });

        return $this->success([
            'parent_option' => [
                'id' => $option->id,
                'value' => $option->value,
                'image_url' => $option->image_url,
                'product_count' => $parentCount,
            ],
            'children' => $childrenWithCounts,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/custom-fields/{customField}/options-with-product-count",
     *     summary="Get all options of a custom field with product counts",
     *     tags={"Custom Fields"},
     *     @OA\Parameter(
     *         name="customField",
     *         in="path",
     *         description="The ID of the custom field (e.g., make or model)",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="value", type="string", example="Acura"),
     *                 @OA\Property(property="image_url", type="string", format="url", example="https://example.com/image.jpg"),
     *                 @OA\Property(property="product_count", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Custom field not found"
     *     )
     * )
     */
    public function fieldWithProductCounts(CustomField $customField)
    {
        $makes = CustomFieldOption::orderByRaw('image_url IS NULL ASC')
            ->where('custom_field_id', $customField->id)
            ->get(['id', 'value', 'image_url'])
            ->map(function ($option) use ($customField) {
                $count = ProductCustomFieldValue::join('products', 'product_custom_field_values.product_id', '=', 'products.id')
                    ->whereNull('products.deleted_at')
                    ->where('product_custom_field_values.custom_field_id', $customField->id)
                    ->where('product_custom_field_values.value', $option->value)
                    ->count();

                $option->product_count = $count;

                return $option;
            });

        return $this->success($makes);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/options/{optionValue}/product-count",
     *     summary="Get product count for a specific custom field option",
     *     description="Returns the product count for a given custom field option value, such as 'C200' or 'Acura'.",
     *     tags={"Custom Field Options"},
     *     @OA\Parameter(
     *         name="optionValue",
     *         in="path",
     *         required=true,
     *         description="The value of the custom field option",
     *         @OA\Schema(type="string", example="C200")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with product count",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=123),
     *             @OA\Property(property="value", type="string", example="C200"),
     *             @OA\Property(property="image_url", type="string", format="url", example="https://example.com/image.jpg"),
     *             @OA\Property(property="product_count", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Option not found"
     *     )
     * )
     */
    public function getOptionWithProductCount(string $optionValue)
    {
        $option = DB::table('custom_field_options')
            ->where('value', $optionValue)
            ->orderByRaw('image_url IS NULL ASC')
            ->first();

        if (!$option) {
            throw new HttpException(404, 'Wrong value');
        }
        // $productCount = DB::table('product_custom_field_values')
        //     ->where('custom_field_id', $option->custom_field_id)
        //     ->where('value', $option->value)
        //     ->count();
        $productCount = DB::table('product_custom_field_values')
            ->join('products', 'products.id', '=', 'product_custom_field_values.product_id')
            ->whereNull('products.deleted_at')
            ->where('product_custom_field_values.custom_field_id', $option->custom_field_id)
            ->where('product_custom_field_values.value', $option->value)
            ->count();

        $results = [
            'id' => $option->id,
            'value' => $option->value,
            'image_url' => $option->image_url,
            'product_count' => $productCount,
        ];

        return $this->success($results);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/options/{optionValue}/products",
     *     summary="Get products by custom field option value",
     *     description="Returns a paginated list of products that match the given custom field option value (e.g., C200).",
     *     tags={"Custom Field Options"},
     *     @OA\Parameter(
     *         name="optionValue",
     *         in="path",
     *         required=true,
     *         description="The value of the custom field option (e.g., C200, Acura)",
     *         @OA\Schema(type="string", example="C200")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Products fetched successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProductSchema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Option not found"
     *     )
     * )
     */
    public function getProductsByOptionValue(string $optionValue)
    {
        $option = CustomFieldOption::where('value', $optionValue)->first();


        if (!$option) {
            throw new HttpException(404, 'Wrong value');
        }

        $products = Product::whereHas('customFieldValues', function ($query) use ($option) {
            $query->where('custom_field_id', $option->custom_field_id)
                  ->where('value', $option->value);
        })->paginate(15);


        return $this->success(ProductResource::collection($products));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/options/search",
     *     summary="Search Custom Field Options",
     *     tags={"Custom Field Options"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SearchCustomFieldOptionRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful search",
     *         @OA\JsonContent(
     *             @OA\Property(property="count", type="integer", example=2),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function search(SearchCustomFieldOptionRequest $request)
    {
        $results = CustomFieldOption::where('custom_field_id', $request->input('custom_field_id'))
            ->where('value', 'like', "{$request->value}%")
            ->get();

        return $this->success($results);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/options/search-makes",
     *     summary="Search Car Makes",
     *     tags={"Custom Field Options"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"value"},
     *             @OA\Property(
     *                 property="value",
     *                 type="string",
     *                 example="Toyota",
     *                 description="The car model to search for."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful search",
     *         @OA\JsonContent(
     *             @OA\Property(property="count", type="integer", example=2),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     )
     * )
     */

    public function searchCarMakes(Request $request)
    {
        $customFieldId = CustomField::where('name', 'make')->first()->id;

        $results = CustomFieldOption::where('custom_field_id', $customFieldId)
            ->where('value', 'like', "{$request->value}%")
            ->get();

        return $this->success($results);
    }
}
