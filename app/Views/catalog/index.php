<h2>Nos plats disponibles</h2>

<?php if ($plats === null): ?>
    <div class="message message-error">
        <strong>Erreur :</strong> Impossible de contacter le service des plats. Verifie que json-server tourne sur le port 3003.
    </div>
<?php elseif ($plats === []): ?>
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

