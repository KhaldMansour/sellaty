<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * @OA\Info(title="Sellaty", version="1.0.0")
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
     * Standardized failure response
     *
     * @param string $message
     * @param int $statusCode
     * @param array|null $errors
     * @return \Illuminate\Http\Response
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
