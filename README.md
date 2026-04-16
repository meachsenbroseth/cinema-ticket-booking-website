# 🎬 Cinema Booking System

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Nuxt](https://img.shields.io/badge/Nuxt.js-00DC82?style=for-the-badge&logo=nuxtdotjs&logoColor=white)
![Vue](https://img.shields.io/badge/Vue.js-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

**A full-featured movie ticket booking platform with real-time seat selection**

[Features](#-features) • [Tech Stack](#-tech-stack) • [Installation](#-installation) • [Screenshots](#-screenshots)

</div>

---

## ✨ Features

### 🎭 For Movie Lovers

| Feature | Description |
|---------|-------------|
| 🎬 **Movie Discovery** | Browse now showing & coming soon movies with rich details |
| 🎟️ **Smart Seat Selection** | Interactive seat map with Standard/VIP pricing |
| 📅 **Date & Time Picker** | Easy showtime selection with cinema filtering |
| 💳 **Secure Checkout** | Multiple payment methods (Card, ABA, Wing, ACLEDA, Cash) |
| 🧾 **Digital Tickets** | QR code tickets for cinema entry |
| 📱 **Responsive Design** | Perfect experience on desktop, tablet & mobile |

### 👑 For Admins

| Feature | Description |
|---------|-------------|
| 📊 **Dashboard** | Overview of system activity |
| 🎬 **Movie Management** | Add/Edit/Delete movies with poster & banner upload |
| 🏢 **Cinema Management** | Manage cinema locations, formats & pricing |
| ⏰ **Showtime Management** | Schedule showtimes with bulk creation |
| 💺 **Hall & Seat Management** | Configure seating layouts and VIP rows |
| 📋 **Booking Management** | View and manage customer bookings |

---

## 🛠️ Tech Stack

### Backend
- Laravel 11.x (RESTful API)
- Laravel Sanctum (Authentication)
- Eloquent ORM
- MySQL Database
- File Storage (Posters/Banners)

### Frontend
- Nuxt 3 (Vue 3)
- Composition API
- Pinia (State Management)
- Tailwind CSS
- Radix Vue UI Components
- Lucide Icons

---

## 🚀 Installation

### Prerequisites

- PHP >= 8.1
- Composer
- Node.js >= 18
- MySQL

### Step 1: Clone the Repository

```bash
git clone https://github.com/your-username/cinema-booking.git
cd cinema-booking

### Step 2: Backend Setup (Laravel)

cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
# DB_DATABASE=cinema_booking
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations and seeders
php artisan migrate --seed

# Create storage link for images
php artisan storage:link

# Start the development server
php artisan serve

API will be available at: http://localhost:8000

### Step 3: Frontend Setup (Nuxt 3)

cd frontend

# Install dependencies
npm install

# Create .env file
echo "API_BASE_URL=http://localhost:8000/api" > .env

# Start development server
npm run dev

Frontend will be available at: http://localhost:3000

# 📸 Screenshots
(https://./screenshots/1.png)
(https://./screenshots/2.png)
(https://./screenshots/3.png)
(https://./screenshots/5.png)

# 🔑 Default Admin Credentials

Role	Email	Password
Admin	admin@legend.com	password

# 📁 Project Structure

cinema-booking/
│
├── backend/                     # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/   # API Controllers
│   │   ├── Models/              # Eloquent Models
│   │   └── ...
│   ├── database/
│   │   └── migrations/          # Database migrations
│   ├── routes/
│   │   └── api.php              # API Routes
│   └── storage/app/public/      # Uploaded images
│
├── frontend/                    # Nuxt 3 App
│   ├── components/              # Vue Components
│   │   ├── admin/               # Admin components
│   │   └── ui/                  # Reusable UI components
│   ├── pages/                   # Application pages
│   │   ├── admin/               # Admin dashboard
│   │   ├── account/             # User account pages
│   │   └── ...
│   ├── stores/                  # Pinia stores
│   │   ├── auth.js              # Authentication
│   │   ├── movie.js             # Movie management
│   │   └── ...
│   └── layouts/                 # Layout templates
│
└── README.md

# 🔗 API Endpoints

Method	Endpoint	Description
POST	/api/login	User login
POST	/api/register	User registration
GET	/api/movies	List movies
GET	/api/movies/{slug}/showtimes	Get movie showtimes
GET	/api/seats/availability	Check seat availability
POST	/api/bookings	Create booking
POST	/api/payments	Process payment
GET	/api/user/bookings	Get user tickets
