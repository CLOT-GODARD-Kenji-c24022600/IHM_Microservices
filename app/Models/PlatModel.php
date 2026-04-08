<?php

declare(strict_types=1);

namespace App\Models;

final class PlatModel
{
    /**
     * @return array<mixed>|null
     */
    public function all(): ?array
    {
        return ApiClient::get(API_URL_PLATS);
    }
}

