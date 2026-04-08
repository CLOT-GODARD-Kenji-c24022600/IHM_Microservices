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

## Architecture MVC (sans framework)

- `index.php`, `creer_menu.php`, `commander.php`: points d'entree (front controllers)
- `app/Controllers`: orchestration HTTP et validation
- `app/Models`: acces aux APIs microservices
- `app/Views`: templates HTML (layout + pages)
- `app/config/config.php`: endpoints des services
- `app/Core/View.php`: rendu commun des vues

Cette organisation garde les URLs historiques tout en separant presentation, logique de controle et acces aux donnees.
