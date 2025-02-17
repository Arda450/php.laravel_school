# Todo-App mit Next.js & Laravel – Installation & Doku

Eine Full-Stack Webanwendung mit modernen Technologien für Benutzerverwaltung und Task-Management.

## Features

### Authentifizierung & Benutzerverwaltung

- Email/Passwort-Anmeldung
- Geschützte Routen
- Profilmanagement
  - Username/Email Änderung
  - Passwort-Update
  - Avatar-Generierung (dicebear API)
  - Konto-Löschung

### Todo-System

- CRUD-Operationen für Tasks
- Prioritätsmanagement
- Status-Tracking

### UI/UX

- Responsive Design
- Dark/Light Mode
- Modern UI mit Shadcn/ui
- Echtzeit-Formularvalidierung

---

## 🛠 Technologie-Stack

### Frontend

- Next.js 14
- TypeScript
- Tailwind CSS
- Shadcn/ui
- NextAuth.js
- React Context

### Backend

- Laravel 10
- SQLite Datenbank
- Laravel Sanctum
- Laravel Eloquent ORM

---

## Voraussetzungen

### 1. Node.js & npm

<details>
<summary>Windows</summary>

1. Node.js von [nodejs.org](https://nodejs.org/) herunterladen
2. Installer ausführen
3. Überprüfen:

```bash
node --version
npm --version
```

</details>

<details>
<summary>macOS</summary>

```bash
brew install node
```

</details>

<details>
<summary>Ubuntu/Debian</summary>

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

</details>

### 2. PHP & Composer

<details>
<summary>Windows</summary>

1. PHP von [windows.php.net](https://windows.php.net/download/)
2. Composer von [getcomposer.org](https://getcomposer.org/download/)
3. PHP zur PATH-Variable hinzufügen
</details>

<details>
<summary>macOS</summary>

```bash
brew install php
brew install composer
```

</details>

<details>
<summary>Ubuntu/Debian</summary>

```bash
sudo apt update
sudo apt install php8.1 php8.1-cli php8.1-common php8.1-sqlite3
sudo apt install composer
```

</details>

### 3. Laravel

```bash
composer global require laravel/installer
```

---

## Installation

### Backend Setup

```bash
# Repository klonen
git clone https://github.com/Arda450/php.laravel_school
cd portfolio_php

# Abhängigkeiten installieren
composer install

# Führe die Migrationen aus
php artisan migrate

# Datenbank seeden
php artisan db:seed

# Server starten
php artisan serve
```

### Frontend Setup

```bash
# Repository klonen
git clone https://github.com/Arda450/next.js_school
cd portfolio_next.js

# Abhängigkeiten installieren
npm install

# .env Datei erstellen
# Erstelle eine .env Datei im Root-Verzeichnis mit folgendem Inhalt:
BACKEND_URL=http://127.0.0.1:8000
AUTH_URL=http://127.0.0.1:3000
AUTH_SECRET=4d6f88334ed8065389045523147659cf785a675023ed84cf536d639e4e8f2f11


# Entwicklungsserver starten
npm run dev
```

### Login Information

- email: alex.dev@example.com
- Password: password

- email: arda.coder@example.com
- Password: password

- email: todo.master@example.com
- Password: password

## 📖 Dokumentation

### API Endpoints

#### 🔓 Öffentliche Endpoints

- `POST /api/register` - Neuen Benutzer registrieren
- `POST /api/auth/login` - Benutzer einloggen

#### 🔒 Geschützte Endpoints (auth:sanctum)

##### Authentifizierung

- `POST /api/auth/logout` - Benutzer ausloggen

##### Benutzerprofil

- `GET /api/user/profile` - Profilinformationen abrufen
- `DELETE /api/user/profile` - Benutzerkonto löschen
- `PATCH /api/user/username` - Benutzernamen aktualisieren
- `PATCH /api/user/email` - E-Mail aktualisieren
- `PATCH /api/user/password` - Passwort aktualisieren
- `PATCH /api/user/avatar` - Avatar aktualisieren

##### Todos

- `GET /api/todos` - Alle Todos abrufen
- `POST /api/todos` - Neues Todo erstellen
- `PATCH /api/todos` - Todo aktualisieren
- `DELETE /api/todos/{id}` - Todo löschen
- `GET /api/todos/{id}` - Todo suchen

##### Sonstiges

- `GET /api/tags` - Verfügbare Tags abrufen
- `GET /api/search` - Benutzersuche

### Authentifizierung

- NextAuth.js für Frontend
- Laravel Sanctum für API
- Session-basierte Auth

### Datenbank

SQLite Datenbank mit Laravel Eloquent ORM

### Styling

- Tailwind CSS für responsive Styles
- Shadcn/ui für UI-Komponenten
- Dark/Light Mode Unterstützung
- Responsive Design für alle Geräte

### Fehler

- Der Fehler mit den Typen im todos/[id]/route.ts konnte nicht gelöst werden. Deswegen ist der npm run build Befehl nicht möglich.
