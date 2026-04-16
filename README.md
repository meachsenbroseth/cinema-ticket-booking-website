# 🎬 Cinema Booking System

A full-stack movie ticket booking application built with:

- ⚙️ Backend: Laravel (API)
- 🎨 Frontend: Nuxt 3 (Vue 3)
- 🔐 Auth: Laravel Sanctum
- 💳 Booking & Payment System

---

## 🚀 Features

- User authentication (login/register)
- Browse movies & showtimes
- Seat selection system (Standard & VIP)
- Booking & payment flow
- Ticket management (Upcoming / Past)
- QR Code ticket display
- Admin-ready backend (Laravel)

---

## 🖼️ Preview

![Home Page](./screenshots/home.png)
![](./screenshots/1.png)
![](./screenshots/2.png)
![](./screenshots/3.png)
![](./screenshots/4.png)
![](./screenshots/5.png)

---

## 🏗️ Tech Stack

### Backend (Laravel)
- RESTful API
- Sanctum Authentication
- MySQL / SQLite
- Eloquent ORM

### Frontend (Nuxt 3)
- Vue 3 + Composition API
- Tailwind CSS
- Radix Vue UI
- Axios (API calls)

---

## 📂 Project Structure
cinema-booking/
│
├── backend/ # Laravel API
├── frontend/ # Nuxt 3 App
└── README.md


---

## ⚙️ Installation

### 1. Clone project

```bash
git clone https://github.com/your-username/cinema-booking.git
cd cinema-booking

2. Backend Setup (Laravel)
cd backend

composer install
cp .env.example .env
php artisan key:generate

# Setup DB
php artisan migrate --seed

php artisan serve


3. Frontend Setup (Nuxt 3)

cd frontend

npm install
npm run dev

🔗 API Base URL

http://localhost:8000/api
