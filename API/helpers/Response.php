<?php

/**
 * Response Helper Class
 *
 * Provides static methods to send standardised JSON API responses
 * with the correct HTTP status codes and headers.
 */
class Response
{
    /**
     * Send a successful JSON response.
     *
     * @param mixed  $data    The payload to return.
     * @param int    $status  HTTP status code (default 200).
     * @param string $message Optional human-readable message.
     */
    public static function success(mixed $data = null, int $status = 200, string $message = 'Success'): void
    {
        http_response_code($status);
        echo json_encode([
            'status'  => 'success',
            'code'    => $status,
            'message' => $message,
            'data'    => $data,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send an error JSON response.
     *
     * @param string $message Human-readable error message.
     * @param int    $status  HTTP status code (default 400).
     * @param mixed  $errors  Optional detailed validation errors.
     */
    public static function error(string $message, int $status = 400, mixed $errors = null): void
    {
        http_response_code($status);
        $body = [
            'status'  => 'error',
            'code'    => $status,
            'message' => $message,
        ];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        echo json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
