<?php

declare(strict_types=1);

namespace App\Models;

final class OrderModel
{
    /**
     * @param array<string, mixed> $order
     * @return array<mixed>|null
     */
    public function create(array $order): ?array
    {
        return ApiClient::post(API_URL_COMMANDES, $order);
    }
}

