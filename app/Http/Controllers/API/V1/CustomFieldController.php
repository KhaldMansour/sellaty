<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\CustomField;
use App\Models\CustomFieldOption;
use App\Models\Product;
use App\Models\ProductCustomFieldValue;
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
     *                 @OA\Property(property="value", type="string", example="Color")
     *             ),
     *             @OA\Property(property="children", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="value", type="string", example="Red")
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

        return $this->success([
            'parent_option' => [
                'id' => $customField->id,
                'value' => $customField->name,
                'image_url' => $customField->image_url,
            ],
            'children' => $customField->options->map(function ($child) {
                return [
                    'id' => $child->id,
                    'value' => $child->value,
                    'image_url' => $child->image_url,
                ];
            }),
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
     *                 @OA\Property(property="value", type="string", example="Red")
     *             ),
     *             @OA\Property(property="children", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=6),
     *                     @OA\Property(property="value", type="string", example="Dark Red")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Custom Field Option not found")
     * )
     */
    public function childrenByOption($customFieldOptionid)
    {
        $option = CustomFieldOption::with('children')->findOrFail($customFieldOptionid);


        return $this->success([
            'parent_option' => [
                'id' => $option->id,
                'value' => $option->value,
                'image_url' => $option->image_url,
            ],
            'children' => $option->children->map(function ($child) {
                return [
                    'id' => $child->id,
                    'value' => $child->value,
                ];
            }),
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
        $makes = CustomFieldOption::where('custom_field_id', $customField->id)
        ->get(['id', 'value' , 'image_url'])
        ->map(function ($option) use ($customField) {
            $count = ProductCustomFieldValue::where('custom_field_id', $customField->id)
                ->where('value', $option->value)
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
            ->first();

        if (!$option) {
            throw new HttpException(404, 'Wrong value');
        }

        $productCount = DB::table('product_custom_field_values')
            ->where('custom_field_id', $option->custom_field_id)
            ->where('value', $option->value)
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
}
