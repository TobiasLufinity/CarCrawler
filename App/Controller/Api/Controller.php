<?php

namespace App\Controller\Api;

use Exception;

abstract class Controller
{

    public abstract function search();

    protected function getPostParam(string $key): ?string
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        if ($data) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
        }
        return null;
    }

    protected function jsonResponse(mixed $data, int $status = 200): string
    {
        header('Content-Type: application/json');
        http_response_code($status);
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function jsonError(string $message, Exception $error = null, int $status = 500): string
    {
        header('Content-Type: application/json');
        http_response_code($status);
        return json_encode([
            'error' => $message,
            'message' => $error->getMessage() ?? $message,
        ], JSON_UNESCAPED_UNICODE);
    }
}