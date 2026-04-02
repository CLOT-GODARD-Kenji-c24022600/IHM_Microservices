<?php
// creer_menu.php
require_once 'includes/api_client.php';
require 'includes/header.php';


// Si l'utilisateur vient de cliquer sur "Créer mon menu"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $createur = $_POST['createur'] ?? '';
    $plats_choisis = $_POST['plats_selectionnes'] ?? [];

    if (!empty($createur) && !empty($plats_choisis)) {
        // On prépare les données pour l'API "Menus"
        $nouveau_menu = [
            "createur" => $createur,
            "date_creation" => date('Y-m-d'),
            "plats" => $plats_choisis
        ];

        // On envoie au serveur sur le port 3004
        $reponse = envoyerDonneesAPI("http://localhost:3004/menus", $nouveau_menu);

        if ($reponse !== null) {
            echo "<div style='color: green; font-weight: bold; padding: 10px; border: 1px solid green; margin-bottom: 20px;'>🎉 Menu créé avec succès !</div>";
        } else {
            echo "<div style='color: red; padding: 10px; border: 1px solid red; margin-bottom: 20px;'>❌ Erreur : Impossible de contacter le serveur de menus.</div>";
        }
    } else {
        echo "<div style='color: orange; padding: 10px; border: 1px solid orange; margin-bottom: 20px;'>⚠️ Veuillez indiquer votre nom et sélectionner au moins un plat.</div>";
    }
}


// On récupère les plats pour générer les cases à cocher (sur le port 3003)
$plats = appelerAPI("http://localhost:3003/plats");
?>

    <h2>Composer un nouveau menu</h2>

<?php if ($plats === null): ?>
    <p style="color: red;"><strong>Erreur :</strong> Impossible de charger les plats. Vérifie que json-server tourne sur le port 3003.</p>
<?php else: ?>
    <form action="creer_menu.php" method="POST">

        <div style="margin-bottom: 15px;">
            <label for="createur"><strong>Votre Nom :</strong></label><br>
            <input type="text" id="createur" name="createur" placeholder="Ex: Jean Dupont" required style="padding: 5px;">
        </div>

        <h3>Cochez les plats à inclure :</h3>
        <ul style="list-style-type: none; padding-left: 0;">
            <?php foreach ($plats as $plat): ?>
                <li style="margin-bottom: 8px;">
                    <label style="cursor: pointer;">
                        <input type="checkbox" name="plats_selectionnes[]" value="<?php echo htmlspecialchars($plat['id']); ?>">
                        <strong><?php echo htmlspecialchars($plat['nom'] ?? 'Plat inconnu'); ?></strong>
                        (<?php echo htmlspecialchars($plat['prix'] ?? '0'); ?> €)
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>

        <button type="submit" style="padding: 10px 20px; cursor: pointer;">Créer mon menu</button>
    </form>
<?php endif; ?>

<?php
require 'includes/footer.php';
?>