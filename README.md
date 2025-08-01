# Laravel Challenge API - Gestion de Profils

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![Tests](https://img.shields.io/badge/Tests-53%20passed-green.svg)](https://phpunit.de)
[![Architecture](https://img.shields.io/badge/Architecture-DDD-purple.svg)](https://en.wikipedia.org/wiki/Domain-driven_design)

Une API REST moderne construite avec Laravel 12 et une architecture Domain-Driven Design (DDD) pour la gestion de profils et commentaires avec authentification sécurisée.

## 🚀 Fonctionnalités

- ✅ **Authentification JWT** avec Laravel Sanctum
- ✅ **Gestion de profils** avec upload d'images
- ✅ **Système de commentaires** avec règles métier
- ✅ **Architecture DDD** avec séparation des responsabilités
- ✅ **Validation typée** avec Form Requests
- ✅ **Tests complets** (Unit + Feature)
- ✅ **API REST** avec réponses JSON standardisées

## 📋 Règles Métier

1. **Authentification obligatoire** pour créer/modifier des profils et commentaires
2. **Un seul commentaire** par administrateur par profil
3. **Endpoint public** pour consulter les profils actifs (sans exposer le statut)
4. **Upload d'images** sécurisé (5MB max, formats validés)
5. **Trois statuts** de profils : inactif, en_attente, actif

## 🏗️ Architecture

```
├── app/                           # Laravel Application Layer
│   ├── Actions/                   # Actions applicatives (orchestration)
│   ├── Http/Controllers/          # Controllers HTTP
│   └── Providers/                 # Service Providers
├── src/                          # Business Layer (DDD)
│   ├── Domain/                   # Logique métier pure
│   │   ├── Administrator/        # Domaine administrateur
│   │   ├── Profile/             # Domaine profil
│   │   └── Comment/             # Domaine commentaire
│   └── Infrastructure/          # Implémentation technique
│       ├── Eloquent/            # Modèles Eloquent
│       ├── Http/                # Requests & Resources
│       └── Repositories/        # Implémentation repositories
├── database/                    # Migrations, Factories, Seeders
└── tests/                      # Tests unitaires et d'intégration
```

## 🛠️ Installation

### Prérequis

- PHP 8.1+
- Composer
- SQLite (ou MySQL/PostgreSQL)
- Git

### Installation locale

```bash
# 1. Cloner le repository
git clone <repository-url>
cd laravel-challenge-api

# 2. Installer les dépendances
composer install

# 3. Configuration de l'environnement
cp .env.example .env
php artisan key:generate

# 4. Configuration de la base de données (SQLite)
touch database/database.sqlite

# Modifier le .env pour SQLite
# DB_CONNECTION=sqlite

# 5. Exécuter les migrations et seeders
php artisan migrate --seed

# 6. Créer le lien symbolique pour les fichiers
php artisan storage:link

# 7. Démarrer le serveur de développement
php artisan serve
```

L'API sera accessible sur `http://localhost:8000`

## 📡 API Endpoints

### 🔓 Endpoints Publics

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `GET` | `/api/health` | Santé de l'API |
| `GET` | `/api/v1/profiles` | Liste des profils actifs |
| `GET` | `/api/v1/profiles/{id}/comments` | Commentaires d'un profil |

### 🔐 Endpoints Authentifiés

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `POST` | `/api/v1/auth/login` | Connexion |
| `POST` | `/api/v1/auth/logout` | Déconnexion |
| `GET` | `/api/v1/auth/me` | Informations utilisateur |
| `POST` | `/api/v1/profiles` | Créer un profil |
| `GET` | `/api/v1/profiles/{id}` | Afficher un profil |
| `PUT` | `/api/v1/profiles/{id}` | Modifier un profil |
| `DELETE` | `/api/v1/profiles/{id}` | Supprimer un profil |
| `POST` | `/api/v1/comments` | Créer un commentaire |
| `GET` | `/api/v1/comments/{id}` | Afficher un commentaire |

## 🔑 Authentification

L'API utilise **Laravel Sanctum** pour l'authentification par tokens.

### Connexion

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@test.com",
    "password": "password123"
  }'
```

**Réponse :**
```json
{
  "success": true,
  "message": "Authentification réussie",
  "data": {
    "administrator": {
      "id": 1,
      "name": "Admin Test",
      "email": "admin@test.com"
    },
    "token": "1|xxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

### Utilisation du token

```bash
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```
## 📮 Collection Postman

Une collection Postman complète est fournie pour tester facilement l'API.

### 📁 Fichiers Postman

```
postman/
├── collection.json          # Collection complète avec tous les endpoints
└── environment.json         # Variables d'environnement (URLs, tokens)
```

### 🚀 Utilisation rapide

1. **Importer la collection** : `postman/collection.json`
2. **Importer les variables l'environnement** : `postman/environment.json`
3. **Démarrer le serveur** : `php artisan serve`
4. **Exécuter les seeders** : `php artisan db:seed`
5. **Tester l'authentification** via Postman

## 📝 Exemples d'utilisation en curl

### Créer un profil

```bash
curl -X POST http://localhost:8000/api/v1/profiles \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Dupont",
    "prenom": "Jean",
    "statut": "actif"
  }'
```

### Créer un profil avec image

```bash
curl -X POST http://localhost:8000/api/v1/profiles \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "nom=Martin" \
  -F "prenom=Pierre" \
  -F "statut=actif" \
  -F "image=@/path/to/image.jpg"
```

### Ajouter un commentaire

```bash
curl -X POST http://localhost:8000/api/v1/comments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "profile_id": 1,
    "contenu": "Excellent profil ! Très professionnel."
  }'
```

## 🔍 Analyse statique (PHPStan)

Pour garantir la qualité et la robustesse de ton code, configure et lance PHPStan :

```bash
# 1. Commencer par l'analyse générale
composer analyse

# 3. Analyser le Domain (ultra-strict)
composer analyse-domain

# 4. Nettoyer les caches si problème
composer analyse-clear
```

## 🧪 Tests

Le projet inclut des tests complets (Unit + Feature).

### Exécuter tous les tests

```bash
php artisan test
```

### Tests par catégorie

```bash
# Tests unitaires (Domain Services)
php artisan test tests/Unit/

# Tests d'intégration (Controllers)
php artisan test tests/Feature/

# Tests avec couverture
php artisan test --coverage
```

### Comptes de test

Après avoir exécuté les seeders, vous pouvez utiliser :

- **Email :** `admin@test.com`
- **Mot de passe :** `password123`

## 🛡️ Validation et Sécurité

### Form Requests

- `LoginRequest` : Validation de la connexion
- `CreateProfileRequest` : Validation création profil
- `UpdateProfileRequest` : Validation modification profil
- `CreateCommentRequest` : Validation création commentaire

### Sécurité

- ✅ Authentification par tokens Sanctum
- ✅ Validation stricte des inputs
- ✅ Protection CSRF
- ✅ Validation des fichiers uploadés
- ✅ Limitation de taille des images (5MB)
- ✅ Types MIME autorisés pour les images

## 🏭 Base de données

### Données de test

```bash
# Régénérer la base avec données de test
php artisan migrate:fresh --seed
```

Les seeders créent :
- 5 administrateurs (dont les comptes de test)
- 20+ profils avec différents statuts
- Commentaires respectant les règles métier

### Modèles

- **Administrator** : Utilisateurs authentifiés
- **Profile** : Profils avec nom, prénom, image, statut
- **Comment** : Commentaires liés aux profils

## 🔧 Développement

### Architecture DDD

- **Domain** : Logique métier pure (DTOs, Services, Repositories)
- **Infrastructure** : Implémentation technique (Eloquent, HTTP)
- **Application** : Orchestration (Actions, Controllers)

### Ajout de fonctionnalités

1. Créer les DTOs dans `src/Domain/{Entity}/Dto/`
2. Définir les Services dans `src/Domain/{Entity}/Services/`
3. Créer les Actions dans `app/Actions/{Entity}/`
4. Implémenter les Controllers dans `app/Http/Controllers/`
5. Ajouter les tests correspondants

## 📦 Dépendances principales

- **Laravel 12** : Framework PHP
- **Laravel Sanctum** : Authentification API
- **PHPUnit** : Tests unitaires
- **Mockery** : Mocking pour les tests

### Base de données

```bash
# Accéder à la base SQLite
php artisan tinker
# Puis : App\Models\Profile::count()
```

## 📄 Licence

Ce projet est développé dans le cadre d'un challenge technique.

## 👥 Contribution

Projet de démonstration - Développé avec une architecture DDD moderne et des bonnes pratiques Laravel.

---

**Développé avec ❤️ et Laravel 12 + DDD**
