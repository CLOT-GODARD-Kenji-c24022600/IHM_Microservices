<?php

declare(strict_types=1);

namespace App\Models;

final class MenuModel
{
    /**
     * @return array<mixed>|null
     */
    public function all(): ?array
    {
        return ApiClient::get(API_URL_MENUS);
    }

    /**
     * @param array<string, mixed> $menu
     * @return array<mixed>|null
     */
    public function create(array $menu): ?array
    {
        return ApiClient::post(API_URL_MENUS, $menu);
    }
}

