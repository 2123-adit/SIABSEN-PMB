<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BaseApiController extends Controller
{
    /**
     * Format validation errors response
     */
    protected function formatValidationErrors(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Data tidak valid',
            'error_code' => 'VALIDATION_ERROR',
            'errors' => $exception->errors()
        ], 422);
    }

    /**
     * Success response with data
     */
    protected function successResponse($data = null, $message = 'Berhasil', $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response
     */
    protected function errorResponse($message = 'Terjadi kesalahan', $errorCode = null, $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errorCode) {
            $response['error_code'] = $errorCode;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Success response with pagination meta
     */
    protected function successWithMeta($data, $meta, $message = 'Berhasil'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta
        ]);
    }
}