<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($data = null, string $message = 'Operation successful', int $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => [],
        ], $code);
    }

    protected function errorResponse(string $message = 'Operation failed', array $errors = [], int $code = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code);
    }

    protected function paginatedResponse($paginator, string $message = 'Data retrieved successfully'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'errors' => [],
        ]);
    }

    protected function validationErrorResponse($validator): \Illuminate\Http\JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            $validator->errors()->messages(),
            422
        );
    }
}
