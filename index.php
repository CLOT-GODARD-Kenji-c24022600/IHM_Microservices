<?php
// index.php
/**
 * Page catalogue: affiche la liste des plats depuis le microservice Plats.
 */
require_once 'includes/config.php';
require_once 'includes/api_client.php';
require 'includes/header.php';

// On interroge ton mock json-server sur le port 3003
$url_plats = API_URL_PLATS;
$plats = appelerAPI($url_plats);
?>

    <h2>Nos plats disponibles</h2>

<?php if ($plats === null): ?>
    <div class="message message-error">
        <strong>Erreur :</strong> Impossible de contacter le service des plats. Verifie que json-server tourne sur le port 3003.
    </div>
<?php else: ?>
    <?php if (empty($plats)): ?>
        <div class="message message-warning">Aucun plat disponible pour le moment.</div>
    <?php else: ?>
        <ul class="list-clean">
            <?php foreach ($plats as $plat): ?>
                <li class="card">
                    <strong><?php echo htmlspecialchars($plat['nom'] ?? 'Plat inconnu'); ?></strong>
                    - <?php echo htmlspecialchars((string) ($plat['prix'] ?? '0')); ?> EUR
                    <br>
                    <em><?php echo htmlspecialchars($plat['description'] ?? ''); ?></em>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php
require 'includes/footer.php';
?>