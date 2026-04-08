<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\MenuModel;
use App\Models\PlatModel;

final class MenuController
{
    public function create(): void
    {
        $message = null;
        $messageType = 'info';
        $createur = '';
        $platsSelectionnes = [];

        $platModel = new PlatModel();
        $menuModel = new MenuModel();

        $plats = $platModel->all();

        $idsDisponibles = $this->extractAvailablePlatIds($plats);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $createur = trim((string) ($_POST['createur'] ?? ''));
            $platsSelectionnes = $this->normalizeSelectedPlats($_POST['plats_selectionnes'] ?? []);

            if ($plats === null) {
                $message = 'Erreur : impossible de charger les plats. Verifie que json-server tourne sur le port 3003.';
                $messageType = 'error';
            } elseif ($createur === '' || strlen($createur) < 2) {
                $message = 'Veuillez indiquer un nom valide (minimum 2 caracteres).';
                $messageType = 'warning';
            } elseif ($platsSelectionnes === []) {
                $message = 'Veuillez selectionner au moins un plat.';
                $messageType = 'warning';
            } else {
                $idsInvalides = array_diff($platsSelectionnes, $idsDisponibles);
                if ($idsInvalides !== []) {
                    $message = 'La selection contient des plats invalides. Recharge la page et recommence.';
                    $messageType = 'error';
                } else {
                    $nouveauMenu = $this->buildMenuPayload($createur, $platsSelectionnes);

                    $reponse = $menuModel->create($nouveauMenu);
                    if ($reponse !== null) {
                        $message = 'Menu cree avec succes.';
                        $messageType = 'success';
                        $createur = '';
                        $platsSelectionnes = [];
                    } else {
                        $message = 'Erreur : impossible de contacter le serveur de menus (port 3004).';
                        $messageType = 'error';
                    }
                }
            }
        }

        View::render('menu/create', [
            'message' => $message,
            'messageType' => $messageType,
            'createur' => $createur,
            'platsSelectionnes' => $platsSelectionnes,
            'plats' => $plats,
        ]);
    }

    /**
     * @param array<mixed>|null $plats
     * @return array<int, string>
     */
    private function extractAvailablePlatIds(?array $plats): array
    {
        if (!is_array($plats)) {
            return [];
        }

        $ids = [];
        foreach ($plats as $plat) {
            if (isset($plat['id'])) {
                $ids[] = (string) $plat['id'];
            }
        }

        return $ids;
    }

    /**
     * @param mixed $platsSelectionnes
     * @return array<int, string>
     */
    private function normalizeSelectedPlats($platsSelectionnes): array
    {
        if (!is_array($platsSelectionnes)) {
            return [];
        }

        return array_values(array_unique(array_map('strval', $platsSelectionnes)));
    }

    /**
     * @param array<int, string> $platsSelectionnes
     * @return array<string, mixed>
     */
    private function buildMenuPayload(string $createur, array $platsSelectionnes): array
    {
        return [
            'createur' => $createur,
            'date_creation' => date('Y-m-d'),
            'plats' => array_map('intval', $platsSelectionnes),
        ];
    }
}

