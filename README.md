# 🛍️ Laravel E-commerce Scraper with AI Analytics

**A powerful Laravel-based e-commerce product scraper with Google Gemini AI analysis**

This project scrapes product data from [Fake Store API](https://fakestoreapi.com/products), stores it in a MySQL database, and provides AI-powered analytics using Google Gemini 2.5 Flash Lite.

![Laravel](https://img.shields.io/badge/Laravel-12.x-red)
![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![Google Gemini](https://img.shields.io/badge/AI-Google%20Gemini-orange)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📋 Features

✅ **Product Scraping** - Fetch products from Fake Store API  
✅ **Database Storage** - Store products with categories, prices, ratings  
✅ **AI Analysis** - Google Gemini AI provides insights on pricing, trends  
✅ **REST API** - Full CRUD API for CRM integration  
✅ **Advanced Filtering** - Filter by price, category, rating  
✅ **Interactive Dashboard** - Beautiful UI with filters and AI suggestions  
✅ **Price Analytics** - Best value, budget-friendly, premium products  
✅ **Category Analysis** - Performance metrics per category  

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- MySQL
- Node.js & NPM (for frontend assets)
- Google Gemini API Key ([Get it free](https://aistudio.google.com/apikey))

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/laravel-scraper.git
cd laravel-scraper

# 2. Install PHP dependencies
composer install

# 3. Install NPM dependencies
npm install

# 4. Copy environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_scraper
DB_USERNAME=root
DB_PASSWORD=

# 7. Add Google Gemini API Key to .env
GOOGLE_GEMINI_API_KEY=your_api_key_here

# 8. Run migrations
php artisan migrate

# 9. Scrape products
curl -X POST http://localhost:8000/api/scrape

# 10. Start the development server
php artisan serve

app/
├── Http/Controllers/
│   ├── HomeController.php          # Main dashboard controller
│   ├── ScraperController.php       # Scraping endpoints
│   └── Api/
│       └── ProductController.php   # Product CRUD API
├── Models/
│   └── Product.php                 # Product model
└── Services/
    └── ScraperService.php          # Scraping logic

routes/
├── web.php                         # Web routes (dashboard)
└── api.php                         # API routes

resources/views/
└── welcome.blade.php               # Main dashboard UI

database/migrations/
└── xxxx_create_products_table.php

![list Create](/public/doc/laravel_s.png)
![list Create](/public/doc/ai.png)
