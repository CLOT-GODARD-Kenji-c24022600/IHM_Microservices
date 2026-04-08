<?php
// creer_menu.php
/**
 * Page de creation de menu: valide la saisie puis cree un menu via l'API Menus.
 */
require_once 'includes/config.php';
require_once 'includes/api_client.php';
require 'includes/header.php';

$message = null;
$messageType = 'info';
$createur = '';
$platsSelectionnes = [];

// On recupere d'abord les plats pour valider les IDs soumis.
$plats = appelerAPI(API_URL_PLATS);
$idsDisponibles = [];
if (is_array($plats)) {
    foreach ($plats as $plat) {
        if (isset($plat['id'])) {
            $idsDisponibles[] = (string) $plat['id'];
        }
    }
}

// Si l'utilisateur vient de cliquer sur "Créer mon menu"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $createur = trim($_POST['createur'] ?? '');
    $platsSelectionnes = $_POST['plats_selectionnes'] ?? [];
    if (!is_array($platsSelectionnes)) {
        $platsSelectionnes = [];
    }

    // Nettoie les IDs et supprime les doublons.
    $platsSelectionnes = array_values(array_unique(array_map('strval', $platsSelectionnes)));

    if ($plats === null) {
        $message = "Erreur : impossible de charger les plats. Verifie que json-server tourne sur le port 3003.";
        $messageType = 'error';
    } elseif ($createur === '' || strlen($createur) < 2) {
        $message = "Veuillez indiquer un nom valide (minimum 2 caracteres).";
        $messageType = 'warning';
    } elseif (empty($platsSelectionnes)) {
        $message = "Veuillez selectionner au moins un plat.";
        $messageType = 'warning';
    } else {
        $idsInvalides = array_diff($platsSelectionnes, $idsDisponibles);
        if (!empty($idsInvalides)) {
            $message = "La selection contient des plats invalides. Recharge la page et recommence.";
            $messageType = 'error';
        } else {
            // On prepare les donnees pour l'API "Menus"
            $nouveauMenu = [
                "createur" => $createur,
                "date_creation" => date('Y-m-d'),
                "plats" => array_map('intval', $platsSelectionnes)
            ];

            // On envoie au serveur sur le port 3004
            $reponse = envoyerDonneesAPI(API_URL_MENUS, $nouveauMenu);

            if ($reponse !== null) {
                $message = "Menu cree avec succes.";
                $messageType = 'success';
                $createur = '';
                $platsSelectionnes = [];
            } else {
                $message = "Erreur : impossible de contacter le serveur de menus (port 3004).";
                $messageType = 'error';
            }
        }
    }
}
?>

<h2>Composer un nouveau menu</h2>

<?php if ($message !== null): ?>
    <div class="message message-<?php echo htmlspecialchars($messageType); ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if ($plats === null): ?>
    <div class="message message-error">
        <strong>Erreur :</strong> Impossible de charger les plats. Verifie que json-server tourne sur le port 3003.
    </div>
<?php elseif (empty($plats)): ?>
    <div class="message message-warning">
        Aucun plat n'est disponible pour composer un menu. Cree d'abord des plats dans le service sur le port 3003.
    </div>
<?php else: ?>
    <form action="creer_menu.php" method="POST" class="stacked-form">
        <div class="field-row">
            <label for="createur"><strong>Votre nom :</strong></label>
            <input
                type="text"
                id="createur"
                name="createur"
                value="<?php echo htmlspecialchars($createur); ?>"
                placeholder="Ex: Jean Dupont"
                required
                minlength="2"
                maxlength="80"
            >
        </div>

        <h3>Cochez les plats a inclure :</h3>
        <ul class="list-clean">
            <?php foreach ($plats as $plat): ?>
                <?php $platId = (string) ($plat['id'] ?? ''); ?>
                <li class="field-row">
                    <label>
                        <input
                            type="checkbox"
                            name="plats_selectionnes[]"
                            value="<?php echo htmlspecialchars($platId); ?>"
                            <?php echo in_array($platId, $platsSelectionnes, true) ? 'checked' : ''; ?>
                        >
                        <strong><?php echo htmlspecialchars($plat['nom'] ?? 'Plat inconnu'); ?></strong>
                        (<?php echo htmlspecialchars((string) ($plat['prix'] ?? '0')); ?> EUR)
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>

        <button type="submit" class="btn-primary">Creer mon menu</button>
    </form>
<?php endif; ?>

<?php
require 'includes/footer.php';
?>