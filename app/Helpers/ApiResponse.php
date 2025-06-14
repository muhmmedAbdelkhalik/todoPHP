<?php

if (!function_exists('apiResponse')) {
    function apiResponse($data = null, $message = null, $success = true, $status = 200, $meta = null)
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];
        if ($meta !== null) {
            $response['meta'] = $meta;
        }
        return response()->json($response, $status);
    }
} 