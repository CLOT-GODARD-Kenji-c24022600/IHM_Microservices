<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = []): void
    {
        $viewFile = APP_ROOT . '/Views/' . $view . '.php';
        if (!is_file($viewFile)) {
            http_response_code(500);
            echo 'Vue introuvable: ' . htmlspecialchars($view, ENT_QUOTES, 'UTF-8');
            return;
        }

        extract($data, EXTR_SKIP);
        require APP_ROOT . '/Views/layout/header.php';
        require $viewFile;
        require APP_ROOT . '/Views/layout/footer.php';
    }
}

