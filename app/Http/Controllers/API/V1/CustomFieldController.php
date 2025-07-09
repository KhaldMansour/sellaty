<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\CustomFieldOption;

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
            ],
            'children' => $customField->options->map(function ($child) {
                return [
                    'id' => $child->id,
                    'value' => $child->value,
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
            ],
            'children' => $option->children->map(function ($child) {
                return [
                    'id' => $child->id,
                    'value' => $child->value,
                ];
            }),
        ]);
    }
}
