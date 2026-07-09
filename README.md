# Budgy — Gestionnaire de dépenses personnelles

Application full-stack de gestion de dépenses personnelles : dashboard avec statistiques,
gestion des transactions et des catégories.

## Stack technique

| Composant | Techno |
|---|---|
| Backend | Symfony 7 (PHP 8.3), API REST en JSON |
| ORM | Doctrine ORM + Migrations |
| Frontend | React 18 + TypeScript, Vite, Tailwind CSS |
| Graphiques | Chart.js (react-chartjs-2) |
| Base de données | PostgreSQL 16 |
| Orchestration | Docker Compose (4 services : `database`, `backend`, `nginx`, `frontend`) |

## Structure du projet

```
Budgy/
├── docker-compose.yml
├── .env                  # variables d'environnement (généré depuis .env.example)
├── backend/               # API Symfony
│   └── src/
│       ├── Entity/        # Category, Transaction
│       ├── Controller/    # CategoryController, TransactionController, StatsController
│       ├── Repository/    # requêtes Doctrine (dont les agrégations de /api/stats)
│       └── DataFixtures/  # jeu de données de démo
└── frontend/              # App React
    └── src/
        ├── pages/          # Dashboard, Transactions, Categories
        └── components/     # cartes, graphiques, modales
```

## Prérequis

- Docker et Docker Compose (v2, plugin `docker compose`)
- Rien d'autre : PHP, Node et PostgreSQL tournent uniquement dans les conteneurs.

## Démarrage

1. Copier le fichier d'environnement d'exemple (déjà fourni avec des valeurs par défaut prêtes à l'emploi) :

   ```bash
   cp .env.example .env
   ```

   Vous pouvez changer `POSTGRES_PASSWORD` et `APP_SECRET` si besoin (pensez à garder
   `DATABASE_URL` cohérent avec `POSTGRES_PASSWORD`).

2. Lancer l'ensemble des services :

   ```bash
   docker compose up --build
   ```

   Cette commande construit les images backend/frontend, démarre PostgreSQL, Nginx,
   PHP-FPM (Symfony) et le serveur de dev Vite.

3. Une fois les conteneurs démarrés, créer le schéma de base de données et charger les
   données de démonstration (dans un **second terminal**, le premier restant occupé par
   les logs de `docker compose up`) :

   ```bash
   # Exécute les migrations Doctrine (crée les tables category / transaction)
   docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction

   # Charge les fixtures : 8 catégories + ~30 transactions de démonstration
   docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction
   ```

4. Accéder à l'application :

   - Frontend : http://localhost:5173
   - API backend (via Nginx) : http://localhost:8080/api

   Les ports peuvent être changés via les variables `FRONTEND_PORT` / `NGINX_PORT` dans `.env`.

## Commandes utiles

```bash
# Arrêter les services (les données PostgreSQL sont conservées dans le volume db_data)
docker compose down

# Arrêter et supprimer aussi les volumes (⚠️ perte des données)
docker compose down -v

# Suivre les logs d'un service en particulier
docker compose logs -f backend

# Ouvrir un shell dans le conteneur backend
docker compose exec backend sh

# Régénérer une migration après modification des entités
docker compose exec backend php bin/console doctrine:migrations:diff

# Rejouer les migrations depuis zéro (utile après un `down -v`)
docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction

# Recharger les fixtures (vide et repeuple les tables)
docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction

# Installer un nouveau paquet frontend (le node_modules du conteneur est un volume dédié)
docker compose exec frontend npm install <paquet>

# Installer un nouveau paquet backend
docker compose exec backend composer require <paquet>
```

## API

Toutes les routes sont préfixées par `/api` et renvoient/consomment du JSON.

### Catégories

| Méthode | Route | Description |
|---|---|---|
| GET | `/api/categories` | Liste des catégories |
| GET | `/api/categories/{id}` | Détail d'une catégorie |
| POST | `/api/categories` | Création (`{ name, color }`) |
| PUT/PATCH | `/api/categories/{id}` | Modification |
| DELETE | `/api/categories/{id}` | Suppression (refusée si des transactions y sont liées) |

### Transactions

| Méthode | Route | Description |
|---|---|---|
| GET | `/api/transactions?month=AAAA-MM&category=ID` | Liste, filtrable par mois et/ou catégorie |
| GET | `/api/transactions/{id}` | Détail |
| POST | `/api/transactions` | Création (`{ label, amount, type, date, categoryId }`) |
| PUT/PATCH | `/api/transactions/{id}` | Modification |
| DELETE | `/api/transactions/{id}` | Suppression |

`type` vaut `income` ou `expense`. `date` est au format `AAAA-MM-JJ`.

### Statistiques

| Méthode | Route | Description |
|---|---|---|
| GET | `/api/stats` | Totaux revenus/dépenses/solde, totaux du mois en cours, répartition des dépenses par catégorie, évolution sur 6 mois |

## Notes de configuration

- **CORS** : géré par `NelmioCorsBundle` (`backend/config/packages/nelmio_cors.yaml`), autorisé
  via la variable d'environnement `CORS_ALLOW_ORIGIN` (regex) injectée par `docker-compose.yml`,
  afin que le frontend (autre origine/port) puisse appeler l'API depuis le navigateur.
- **Hot-reload** : le code source de `backend/` et `frontend/` est monté en bind mount dans les
  conteneurs ; `vendor/`, `var/` et `node_modules/` restent dans des volumes dédiés pour ne pas
  être écrasés par le mount et éviter de les réinstaller à chaque redémarrage.
- **Persistance BDD** : les données PostgreSQL sont stockées dans le volume nommé `db_data`,
  qui survit aux `docker compose down` (mais pas à `docker compose down -v`).
