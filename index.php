<?php
// index.php
require_once 'includes/api_client.php';
require 'includes/header.php';

// On interroge ton mock json-server sur le port 3003
$url_plats = "http://localhost:3003/plats";
$plats = appelerAPI($url_plats);
?>

    <h2>Nos plats disponibles</h2>

<?php if ($plats === null): ?>
    <p style="color: red;"><strong>Erreur :</strong> Impossible de contacter le service des plats. Vérifie que json-server tourne sur le port 3003.</p>
<?php else: ?>
    <ul>
        <?php foreach ($plats as $plat): ?>
            <li>
                <strong><?php echo htmlspecialchars($plat['nom'] ?? 'Plat inconnu'); ?></strong>
                - <?php echo htmlspecialchars($plat['prix'] ?? '0'); ?> €
                <br>
                <em><?php echo htmlspecialchars($plat['description'] ?? ''); ?></em>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
require 'includes/footer.php';
?>