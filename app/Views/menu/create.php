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
<?php elseif ($plats === []): ?>
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

