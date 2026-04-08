<?php
// commander.php
/**
 * Page de commande: construit une commande client et l'enregistre via l'API Commandes.
 */
require_once 'includes/config.php';
require_once 'includes/api_client.php';
require 'includes/header.php';

$message = null;
$messageType = 'info';
$nomClient = '';
$adresseLivraison = '';
$dateLivraison = '';
$dateMinLivraison = date('Y-m-d');
$quantitesSaisies = [];

$plats = appelerAPI(API_URL_PLATS);
$menus = appelerAPI(API_URL_MENUS);

$prixParPlat = [];
if (is_array($plats)) {
    foreach ($plats as $plat) {
        if (isset($plat['id'])) {
            $prixParPlat[(string) $plat['id']] = (float) ($plat['prix'] ?? 0);
        }
    }
}

$menusParId = [];
if (is_array($menus)) {
    foreach ($menus as $menu) {
        if (isset($menu['id'])) {
            $menusParId[(string) $menu['id']] = $menu;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomClient = trim($_POST['nom_client'] ?? '');
    $adresseLivraison = trim($_POST['adresse_livraison'] ?? '');
    $dateLivraison = trim($_POST['date_livraison'] ?? '');
    $quantites = $_POST['quantites'] ?? [];

    if (!is_array($quantites)) {
        $quantites = [];
    }

    foreach ($quantites as $menuId => $quantiteBrute) {
        $menuId = (string) $menuId;
        $quantitesSaisies[$menuId] = max(0, min(20, (int) $quantiteBrute));
    }

    $menusCommandes = [];
    $montantTotal = 0;

    foreach ($quantitesSaisies as $menuId => $quantite) {
        $menuId = (string) $menuId;

        if ($quantite <= 0) {
            continue;
        }

        if (!isset($menusParId[$menuId])) {
            continue;
        }

        $menu = $menusParId[$menuId];
        $platsDuMenu = $menu['plats'] ?? [];
        if (!is_array($platsDuMenu)) {
            $platsDuMenu = [];
        }

        $prixUnitaire = 0;
        foreach ($platsDuMenu as $platId) {
            $platId = (string) $platId;
            $prixUnitaire += $prixParPlat[$platId] ?? 0;
        }

        $sousTotal = $prixUnitaire * $quantite;
        $montantTotal += $sousTotal;

        $menusCommandes[] = [
            'menu_id' => (int) $menuId,
            'quantite' => $quantite,
            'prix_unitaire' => round($prixUnitaire, 2),
            'sous_total' => round($sousTotal, 2)
        ];
    }

    $dateLivraisonValide = false;
    if ($dateLivraison !== '') {
        $dateObjet = DateTime::createFromFormat('Y-m-d', $dateLivraison);
        $dateLivraisonValide = $dateObjet instanceof DateTime && $dateObjet->format('Y-m-d') === $dateLivraison;
    }

    if ($menus === null || $plats === null) {
        $message = 'Erreur : impossible de charger les menus ou les plats depuis les services.';
        $messageType = 'error';
    } elseif (empty($menus)) {
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
    } elseif (empty($menusCommandes)) {
        $message = 'Veuillez selectionner au moins un menu avec une quantite > 0.';
        $messageType = 'warning';
    } else {
        $nouvelleCommande = [
            'client' => $nomClient,
            'date_commande' => date('Y-m-d'),
            'date_livraison' => $dateLivraison,
            'adresse_livraison' => $adresseLivraison,
            'menus' => $menusCommandes,
            'montant_total' => round($montantTotal, 2)
        ];

        $resultat = envoyerDonneesAPI(API_URL_COMMANDES, $nouvelleCommande);
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
?>

<h2>Passer une commande</h2>

<?php if ($message !== null): ?>
    <div class="message message-<?php echo htmlspecialchars($messageType); ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if ($menus === null || $plats === null): ?>
    <div class="message message-error">
        Verifie que json-server tourne sur les ports 3003, 3004 et 3005.
    </div>
<?php else: ?>
    <form action="commander.php" method="POST" class="stacked-form">
        <div class="field-row">
            <label for="nom_client"><strong>Nom du client :</strong></label>
            <input type="text" id="nom_client" name="nom_client" required minlength="2" maxlength="80" value="<?php echo htmlspecialchars($nomClient); ?>">
        </div>

        <div class="field-row">
            <label for="adresse_livraison"><strong>Adresse de livraison :</strong></label>
            <textarea id="adresse_livraison" name="adresse_livraison" required minlength="8" maxlength="250" rows="3"><?php echo htmlspecialchars($adresseLivraison); ?></textarea>
        </div>

        <div class="field-row">
            <label for="date_livraison"><strong>Date de livraison :</strong></label>
            <input type="date" id="date_livraison" name="date_livraison" required min="<?php echo htmlspecialchars($dateMinLivraison); ?>" value="<?php echo htmlspecialchars($dateLivraison); ?>">
        </div>

        <?php if (empty($menus)): ?>
            <div class="message message-warning">Aucun menu n'est encore disponible. Passe d'abord par la page de creation de menu.</div>
        <?php else: ?>
        <h3>Choisissez les menus et quantites</h3>
        <table class="table">
            <thead>
            <tr>
                <th>Menu</th>
                <th>Createur</th>
                <th>Nb plats</th>
                <th>Quantite</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($menus as $menu): ?>
                <?php
                $menuId = (string) ($menu['id'] ?? '');
                $platsDuMenu = $menu['plats'] ?? [];
                $nbPlats = is_array($platsDuMenu) ? count($platsDuMenu) : 0;
                ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($menuId); ?></td>
                    <td><?php echo htmlspecialchars($menu['createur'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars((string) $nbPlats); ?></td>
                    <td>
                        <?php $inputId = 'quantite_menu_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $menuId); ?>
                        <label for="<?php echo htmlspecialchars($inputId); ?>">Quantite menu <?php echo htmlspecialchars($menuId); ?></label>
                        <input id="<?php echo htmlspecialchars($inputId); ?>" type="number" min="0" max="20" name="quantites[<?php echo htmlspecialchars($menuId); ?>]" value="<?php echo htmlspecialchars((string) ($quantitesSaisies[$menuId] ?? 0)); ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="field-row">
            <button type="submit" class="btn-primary">Valider la commande</button>
        </div>
    </form>
<?php endif; ?>

<?php
require 'includes/footer.php';
?>

