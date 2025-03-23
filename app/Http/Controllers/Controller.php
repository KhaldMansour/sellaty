<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * @OA\Info(title="Sellaty", version="1.0.0")
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Please provide your JWT token in the Authorization header."
 * )
 */
abstract class Controller
{
    /**
     * Standardized success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\Response
     */
    public function success($data = null, $message = 'Success', $statusCode = Response::HTTP_OK)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $statusCode);
    }

    /**
     * @OA\Schema(
     *     schema="ErrorResponseSchema",
     *     type="object",
     *     @OA\Property(
     *         property="status",
     *         type="string",
     *         example="error"
     *     ),
     *     @OA\Property(
     *         property="message",
     *         type="string",
     *         example="An error occurred"
     *     ),
     *     @OA\Property(
     *         property="data",
     *         type="array",
     *         description="Array of error-related data",
     *         @OA\Items(type="string"),
     *         example={}
     *     ),
     *     @OA\Property(
     *         property="errors",
     *         type="array",
     *         description="Array of specific error messages",
     *         @OA\Items(type="string"),
     *         example={}
     *     )
     * )
     */
    public function failure($message = 'Failure', $statusCode = Response::HTTP_BAD_REQUEST, $errors = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $statusCode);
    }
}
