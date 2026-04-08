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

        <?php if ($menus === []): ?>
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

