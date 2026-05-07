# 🏪 Rysgally Hasap Market System

A comprehensive market management system built with Laravel and Tauri for retail and wholesale operations with multi-till support, inventory management, and electronic scale integration.

## ✨ Features

### 🎯 Core Functionality
- **Multi-Role System**: Admin, Salesman, Storage, and Wholesale roles
- **Multi-Till Support**: Multiple POS terminals connected via Ethernet
- **Multi-Language**: English, Russian, and Turkmen localization
- **License Management**: Secure licensing system for device authorization

### 📦 Inventory Management
- Product management with categories and barcodes
- Storage tracking with quantity management
- Weighable vs. unit-based products
- Expiry date tracking
- Batch number management

### 💰 Sales & POS
- Point of Sale system with cart functionality
- Discount management
- Shift management with opening/closing
- Receipt generation (thermal printing)
- Customer management

### 📊 Wholesale Operations
- Wholesale invoice management
- Wholesale storage tracking
- Transfer between wholesale and retail
- Excel export functionality

### 🏢 Admin Dashboard
- Financial overview cards
- Till management and monitoring
- Expense tracking
- Revenue reports
- Shift logs and statistics

### ⚖️ Scale Integration
- Electronic scale integration via Ethernet
- Automatic export of weighable products
- Manual export button in storage interface
- Support for various scale protocols (RS232, TCP/IP)

## 🚀 Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Blade templates with Vite
- **Desktop App**: Tauri (Rust + WebView)
- **Database**: SQLite (development) / MySQL (production)
- **Build Tool**: GitHub Actions for CI/CD

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js 18+
- MySQL 8.0+ (production)
- Rust toolchain (for Tauri builds)

## 🔧 Installation

### Development Setup

1. **Clone the repository**
```bash
git clone <repository-url>
cd rysgally-hasap-market
```

2. **Install PHP dependencies**
```bash
cd src-tauri/resources/rysgally-hasap-market
composer install
```

3. **Install Node.js dependencies**
```bash
npm install
```

4. **Copy environment file**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Run migrations**
```bash
php artisan migrate
php artisan db:seed
```

6. **Build frontend assets**
```bash
npm run build
```

7. **Start development server**
```bash
php artisan serve
```

### Tauri Desktop App

1. **Navigate to Tauri directory**
```bash
cd src-tauri
```

2. **Install dependencies**
```bash
npm install
```

3. **Run development**
```bash
npm run tauri dev
```

4. **Build for production**
```bash
npm run tauri build
```

## 🌐 Production Deployment

### Server Setup (Windows)

1. **Install MySQL**
   - Download MySQL Installer from https://dev.mysql.com/downloads/installer/
   - Install MySQL Server
   - Create database and user

2. **Configure MySQL**
```sql
CREATE DATABASE market_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'market_user'@'%' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON market_system.* TO 'market_user'@'%';
FLUSH PRIVILEGES;
```

3. **Install Tauri Application**
   - Download `.exe` from GitHub Actions artifacts
   - Install on server
   - Configure `config.json` in `%APPDATA%\rysgally-hasap-market\`

4. **Network Setup**
   - Configure static IP addresses
   - Connect all tills via Ethernet switch
   - Ensure MySQL port 3306 is accessible

### Till Configuration

1. **Install Tauri Application** on each till
2. **Configure config.json** to connect to server MySQL
3. **Register till** in admin panel
4. **Activate license** for each till

## 📁 Project Structure

```
├── src-tauri/
│   ├── src/                 # Rust source code
│   └── resources/
│       └── rysgally-hasap-market/
│           ├── app/         # Laravel application
│           ├── config/      # Configuration files
│           ├── database/    # Migrations and seeders
│           ├── resources/   # Views and language files
│           └── routes/      # Route definitions
└── README.md
```

## 🔑 Default Credentials

- **Admin**: admin / admin123
- **Salesman**: salesman / password
- **Storage**: storage / password
- **Wholesale**: wholesale / password

## 🌍 Localization

The system supports three languages:
- English (en)
- Russian (ru)
- Turkmen (tm)

Language can be switched via the language selector in the UI.

## 📊 Database Schema

Key tables:
- `users` - User accounts with roles
- `products` - Product catalog
- `storage` - Inventory management
- `sales` - Sales transactions
- `tills` - POS terminal management
- `licenses` - License management
- `shifts` - Shift tracking
- `expenses` - Expense tracking

## 🔧 Configuration

### Environment Variables

```env
APP_NAME=MarketSystem
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=market_system
DB_USERNAME=market_user
DB_PASSWORD=your_password

# Scale Configuration
SCALE_IP=192.168.1.100
SCALE_PORT=8080
SCALE_TIMEOUT=10
SCALE_AUTO_EXPORT_ON_CREATE=true
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is proprietary software. All rights reserved.

## 👥 Authors

- **Anna Gurban** - Initial development

## 🙏 Acknowledgments

- Laravel Framework
- Tauri Team
- Bootstrap for UI components
- All contributors
