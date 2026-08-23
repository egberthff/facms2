# Capstone FACMS Setup Instructions

Follow these step-by-step instructions to clone the repository and get the application running locally on your machine.

---

## 📋 Prerequisites
Ensure you have the following installed on your system before starting:
- **PHP** ^8.2
- **Composer** ^2.8
- **Node.js** v24.12 & **NPM** ^10.6
- **Database Server** MySQL ^8.2 or XAMPP

---

## 🚀 Installation Steps

### Step 1: Clone the Repository
Open your terminal, navigate to your projects directory, and run the clone command:

```bash
git clone https://github.com/egberthff/facms2.git
```

> [!NOTE]
> - Enter your GitHub username when prompted for a username.
> - Enter your Personal Access Token (PAT) when prompted for a password.

Once cloned, move into the project directory:
```bash
cd facms2
```

### Step 2: Install PHP Dependencies
Download the backend PHP packages required by the application:
```bash
composer update
composer install
```

### Step 3: Configure the Environment File
Create a local environment configuration file from the provided template:
```bash
cp .env.example .env
```

Open the `.env` file in your code editor and update the database settings to match your local setup:
```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=facms2
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
```

> [!IMPORTANT]
> Make sure to create an empty database with the name specified in `DB_DATABASE` (`facms2`) before moving to the next step.

### Step 4: Generate the Application Key
Generate a unique secure encryption key for your local instance:
```bash
php artisan key:generate
```

### Step 5: Run Database Migrations
Build your local database schema and populate it with initial data:
```bash
php artisan migrate --seed
```

### Step 6: Install and Build Frontend Assets
Install the frontend node packages and run the asset compilation process:
```bash
npm install
npm run dev
```
*Keep this terminal window running in the background to automatically compile any frontend changes.*

### Step 7: Start the Application Server
Open a new terminal window in the project directory and start the local development server:
```bash
php artisan serve
```

---

## ✅ Verification
Your local environment is now ready! Open your browser and navigate to:
👉 [http://127.0.0.1:8000](http://127.0.0.1:8000)

### Test Accounts

| Role | Username | Password |
| :--- | :--- | :--- |
| **Member** | `member@bisu.edu.ph` | `123456` |
| **President** | `president@bisu.edu.ph` | `12345` |
