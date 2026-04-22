# Plan d'implémentation - Système de Transaction Bancaire (API)

## 1. Cahier des Charges

### Spécifications Fonctionnelles
- **Gestion des comptes :**
    - Création d'un compte bancaire pour un utilisateur authentifié.
    - Consultation de la liste des comptes de l'utilisateur.
    - Consultation du solde d'un compte.
- **Transactions :**
    - Effectuer un dépôt sur un compte.
    - Effectuer un retrait d'un compte (avec vérification du solde suffisant).
    - Effectuer un virement entre deux comptes (interne ou externe).
    - Historique des transactions pour chaque compte.

### Spécifications Non-Fonctionnelles
- **Sécurité :** Authentification via Laravel Sanctum. Seul le propriétaire du compte peut voir ses informations et effectuer des transactions.
- **Fiabilité :** Utilisation des transactions de base de données (ACID) pour garantir l'intégrité des données lors des dépôts et retraits.
- **Performance :** Réponses API rapides (moins de 200ms pour les transactions simples).
- **Validation :** Validation stricte des entrées (montants positifs, existence des comptes).

## 2. Analyse du Système

### Acteurs
- **Client :** Utilisateur final qui possède des comptes, effectue des dépôts et des retraits.
- **Système :** Gère la logique métier, la validation et la persistance des données.

### Cas d'Utilisation (Use Cases)
- **UC1 : Créer un compte** (Client)
- **UC2 : Lister mes comptes** (Client)
- **UC3 : Déposer de l'argent** (Client)
- **UC4 : Retirer de l'argent** (Client)
- **UC5 : Consulter l'historique** (Client)

## 3. Conception Technique

### Schéma de Base de Données
- **Table `accounts` :**
    - `id` (PK)
    - `user_id` (FK vers users)
    - `account_number` (string, unique)
    - `balance` (decimal, default 0)
    - `type` (savings, current)
- **Table `transactions` :**
    - `id` (PK)
    - `account_id` (FK vers accounts)
    - `type` (deposit, withdrawal)
    - `amount` (decimal)
    - `description` (string, optional)
    - `created_at` (timestamp)

### API Endpoints (REST)
- `POST /api/accounts` : Créer un nouveau compte.
- `GET /api/accounts` : Liste des comptes de l'utilisateur.
- `GET /api/accounts/{id}` : Détails d'un compte spécifique.
- `POST /api/accounts/{id}/deposit` : Effectuer un dépôt.
- `POST /api/accounts/{id}/withdraw` : Effectuer un retrait.
- `POST /api/accounts/{id}/transfer` : Effectuer un virement vers un autre compte.

## 4. Étapes d'Implémentation

### Phase 1 : Préparation & Modèles
1. Créer la migration et le modèle `Account`.
2. Créer la migration et le modèle `Transaction`.
3. Définir les relations Eloquent dans le modèle `User`.

### Phase 2 : Logique Métier (Controllers)
1. Créer `AccountController` pour la gestion des comptes.
2. Créer `TransactionController` (ou intégrer dans AccountController) pour les dépôts/retraits.
3. Implémenter la validation des requêtes (Form Requests).

### Phase 3 : Sécurité & Routes
1. Configurer les routes dans `routes/api.php` sous le middleware `auth:sanctum`.
2. Implémenter des Policies pour s'assurer que l'utilisateur n'accède qu'à ses propres comptes.

### Phase 4 : Tests & Validation
1. Créer des tests unitaires pour les transactions (dépôt/retrait).
2. Créer des tests de bout en bout (Feature tests) pour l'API.
