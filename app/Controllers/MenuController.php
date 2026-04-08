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

        $idsDisponibles = [];
        if (is_array($plats)) {
            foreach ($plats as $plat) {
                if (isset($plat['id'])) {
                    $idsDisponibles[] = (string) $plat['id'];
                }
            }
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $createur = trim((string) ($_POST['createur'] ?? ''));
            $platsSelectionnes = $_POST['plats_selectionnes'] ?? [];
            if (!is_array($platsSelectionnes)) {
                $platsSelectionnes = [];
            }

            $platsSelectionnes = array_values(array_unique(array_map('strval', $platsSelectionnes)));

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
                    $nouveauMenu = [
                        'createur' => $createur,
                        'date_creation' => date('Y-m-d'),
                        'plats' => array_map('intval', $platsSelectionnes),
                    ];

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
}

