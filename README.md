# IHM_Microservices

Interface web (PHP/HTML/CSS) du projet microservices "livraison de repas".

## Prerequis

- PHP 8+ (ou version compatible avec `file_get_contents` en HTTP)
- Node.js + npm (pour `json-server`)

## Services attendus

- `Plats & Utilisateurs` sur `http://localhost:3003`
- `Menus` sur `http://localhost:3004`
- `Commandes` sur `http://localhost:3005`

Le composant IHM consomme ces APIs REST.

## Lancer les mocks json-server

Dans trois terminaux distincts, depuis le dossier contenant les fichiers JSON de mock:

Les fichiers `plats-utilisateurs.json`, `menus.json` et `commandes.json` sont fournis a la racine de ce projet.

```bash
npx json-server --watch plats-utilisateurs.json --port 3003
npx json-server --watch menus.json --port 3004
npx json-server --watch commandes.json --port 3005
```

## Lancer l'IHM en local

Depuis le dossier du projet:

```bash
php -S localhost:8000
```

Puis ouvrir:

- `http://localhost:8000/index.php`

## Pages disponibles

- `index.php`: catalogue des plats
- `creer_menu.php`: creation d'un menu (nom createur + plats)
- `commander.php`: creation d'une commande (client, adresse, date, quantites)

## Architecture MVC legere sans framework

Cette partie IHM utilise une architecture **MVC legere** :

- `index.php`, `creer_menu.php`, `commander.php` : **front controllers** qui demarrent la page cible.
- `app/Controllers` : **controleurs** qui recoivent la requete, valident les donnees du formulaire et orchestrent le flux.
- `app/Models` : **modele d'acces aux donnees** qui interroge les microservices REST via `ApiClient`.
- `app/Views` : **vues** HTML qui affichent uniquement les donnees preparees par le controleur.
- `app/Core/View.php` : rendu commun avec le layout header/footer.
- `app/config/config.php` : centralisation des URLs des services.

### Pourquoi ce choix ?

- **Separation des responsabilites** : l'HTML reste dans les vues, la logique de traitement reste dans les controleurs, et l'acces aux APIs reste dans les modeles.
- **Code plus simple a lire et a maintenir** : chaque fichier a un role clair, ce qui facilite les corrections avant rendu.
- **Adaptation au projet** : comme l'IHM consomme des microservices REST, un MVC leger suffit sans ajouter de framework lourd.
- **Bonne defense a l'oral** : cette structure montre une vraie organisation logicielle tout en restant pragmatique pour un projet scolaire.

Cette organisation garde les URLs historiques tout en separant presentation, logique de controle et acces aux donnees.
