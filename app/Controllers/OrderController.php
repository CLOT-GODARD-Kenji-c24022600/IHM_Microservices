<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\MenuModel;
use App\Models\OrderModel;
use App\Models\PlatModel;
use DateTime;

final class OrderController
{
    public function create(): void
    {
        $message = null;
        $messageType = 'info';
        $nomClient = '';
        $adresseLivraison = '';
        $dateLivraison = '';
        $dateMinLivraison = date('Y-m-d');
        $quantitesSaisies = [];

        $platModel = new PlatModel();
        $menuModel = new MenuModel();
        $orderModel = new OrderModel();

        $plats = $platModel->all();
        $menus = $menuModel->all();

        $prixParPlat = $this->buildPrixParPlat($plats);
        $menusParId = $this->buildMenusParId($menus);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $nomClient = trim((string) ($_POST['nom_client'] ?? ''));
            $adresseLivraison = trim((string) ($_POST['adresse_livraison'] ?? ''));
            $dateLivraison = trim((string) ($_POST['date_livraison'] ?? ''));
            $quantitesSaisies = $this->normalizeQuantites($_POST['quantites'] ?? []);
            $commandeResultat = $this->buildOrderLines($quantitesSaisies, $menusParId, $prixParPlat);
            $menusCommandes = $commandeResultat['menusCommandes'];
            $montantTotal = $commandeResultat['montantTotal'];

            $dateLivraisonValide = false;
            if ($dateLivraison !== '') {
                $dateObjet = DateTime::createFromFormat('Y-m-d', $dateLivraison);
                $dateLivraisonValide = $dateObjet instanceof DateTime && $dateObjet->format('Y-m-d') === $dateLivraison;
            }

            if ($menus === null || $plats === null) {
                $message = 'Erreur : impossible de charger les menus ou les plats depuis les services.';
                $messageType = 'error';
            } elseif ($menus === []) {
                $message = 'Aucun menu disponible pour le moment. Cree un menu avant de passer commande.';
                $messageType = 'warning';
            } elseif ($nomClient === '' || strlen($nomClient) < 2) {
                $message = 'Veuillez indiquer un nom valide (minimum 2 caracteres).';
                $messageType = 'warning';
            } elseif ($adresseLivraison === '' || strlen($adresseLivraison) < 8) {
                $message = 'Veuillez renseigner une adresse de livraison plus precise.';
                $messageType = 'warning';
            } elseif ($dateLivraison === '') {
                $message = 'Veuillez choisir une date de livraison.';
                $messageType = 'warning';
            } elseif (!$dateLivraisonValide) {
                $message = 'La date de livraison est invalide.';
                $messageType = 'warning';
            } elseif ($dateLivraison < $dateMinLivraison) {
                $message = 'La date de livraison ne peut pas etre dans le passe.';
                $messageType = 'warning';
            } elseif ($menusCommandes === []) {
                $message = 'Veuillez selectionner au moins un menu avec une quantite > 0.';
                $messageType = 'warning';
            } else {
                $nouvelleCommande = [
                    'client' => $nomClient,
                    'date_commande' => date('Y-m-d'),
                    'date_livraison' => $dateLivraison,
                    'adresse_livraison' => $adresseLivraison,
                    'menus' => $menusCommandes,
                    'montant_total' => round($montantTotal, 2),
                ];

                $resultat = $orderModel->create($nouvelleCommande);
                if ($resultat !== null) {
                    $message = 'Commande enregistree avec succes.';
                    $messageType = 'success';
                    $nomClient = '';
                    $adresseLivraison = '';
                    $dateLivraison = '';
                    $quantitesSaisies = [];
                } else {
                    $message = 'Erreur : impossible de contacter le service commandes (port 3005).';
                    $messageType = 'error';
                }
            }
        }

        View::render('order/create', [
            'message' => $message,
            'messageType' => $messageType,
            'nomClient' => $nomClient,
            'adresseLivraison' => $adresseLivraison,
            'dateLivraison' => $dateLivraison,
            'dateMinLivraison' => $dateMinLivraison,
            'quantitesSaisies' => $quantitesSaisies,
            'plats' => $plats,
            'menus' => $menus,
        ]);
    }

    /**
     * @param array<mixed>|null $plats
     * @return array<string, float>
     */
    private function buildPrixParPlat(?array $plats): array
    {
        $prixParPlat = [];
        if (!is_array($plats)) {
            return $prixParPlat;
        }

        foreach ($plats as $plat) {
            if (isset($plat['id'])) {
                $prixParPlat[(string) $plat['id']] = (float) ($plat['prix'] ?? 0);
            }
        }

        return $prixParPlat;
    }

    /**
     * @param array<mixed>|null $menus
     * @return array<string, array<mixed>>
     */
    private function buildMenusParId(?array $menus): array
    {
        $menusParId = [];
        if (!is_array($menus)) {
            return $menusParId;
        }

        foreach ($menus as $menu) {
            if (isset($menu['id'])) {
                $menusParId[(string) $menu['id']] = $menu;
            }
        }

        return $menusParId;
    }

    /**
     * @param mixed $quantites
     * @return array<string, int>
     */
    private function normalizeQuantites($quantites): array
    {
        if (!is_array($quantites)) {
            return [];
        }

        $quantitesSaisies = [];
        foreach ($quantites as $menuId => $quantiteBrute) {
            $quantitesSaisies[(string) $menuId] = max(0, min(20, (int) $quantiteBrute));
        }

        return $quantitesSaisies;
    }

    /**
     * @param array<string, int> $quantitesSaisies
     * @param array<string, array<mixed>> $menusParId
     * @param array<string, float> $prixParPlat
     * @return array{menusCommandes: array<int, array<string, int|float>>, montantTotal: float}
     */
    private function buildOrderLines(array $quantitesSaisies, array $menusParId, array $prixParPlat): array
    {
        $menusCommandes = [];
        $montantTotal = 0.0;

        foreach ($quantitesSaisies as $menuId => $quantite) {
            if ($quantite <= 0 || !isset($menusParId[$menuId])) {
                continue;
            }

            $menu = $menusParId[$menuId];
            $platsDuMenu = $menu['plats'] ?? [];
            if (!is_array($platsDuMenu)) {
                $platsDuMenu = [];
            }

            $prixUnitaire = 0.0;
            foreach ($platsDuMenu as $platId) {
                $prixUnitaire += $prixParPlat[(string) $platId] ?? 0;
            }

            $sousTotal = $prixUnitaire * $quantite;
            $montantTotal += $sousTotal;

            $menusCommandes[] = [
                'menu_id' => (int) $menuId,
                'quantite' => $quantite,
                'prix_unitaire' => round($prixUnitaire, 2),
                'sous_total' => round($sousTotal, 2),
            ];
        }

        return [
            'menusCommandes' => $menusCommandes,
            'montantTotal' => round($montantTotal, 2),
        ];
    }
}

