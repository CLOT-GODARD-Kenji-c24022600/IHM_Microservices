<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\PlatModel;

final class CatalogController
{
    public function index(): void
    {
        $platModel = new PlatModel();
        $plats = $platModel->all();

        View::render('catalog/index', [
            'plats' => $plats,
        ]);
    }
}

