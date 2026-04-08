<?php

declare(strict_types=1);

namespace App\Models;

final class ApiClient
{
    /**
     * @param array<int, string>|null $headers
     */
    private static function extractHttpCode($headers): int
    {
        if (!is_array($headers) || $headers === []) {
            return 0;
        }

        foreach ($headers as $header) {
            if (preg_match('/HTTP\/\S+\s+(\d{3})/', $header, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return 0;
    }

    /**
     * @return array<mixed>|null
     */
    public static function get(string $url): ?array
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\n",
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ];

        $context = stream_context_create($options);
        $jsonData = @file_get_contents($url, false, $context);
        $httpCode = self::extractHttpCode($http_response_header ?? []);

        if ($jsonData === false || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        $decoded = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<mixed>|null
     */
    public static function post(string $url, array $payload): ?array
    {
        $encoded = json_encode($payload);
        if ($encoded === false) {
            return null;
        }

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\nAccept: application/json\r\n",
                'method' => 'POST',
                'content' => $encoded,
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        $httpCode = self::extractHttpCode($http_response_header ?? []);

        if ($result === false || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        if (trim($result) === '') {
            return [];
        }

        $decoded = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return [];
        }

        return $decoded;
    }
}

