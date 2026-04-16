<div align="center">
  
  # 🎬 Cinema Booking System
  
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/Nuxt.js-00DC82?style=for-the-badge&logo=nuxtdotjs&logoColor=white" />
  <img src="https://img.shields.io/badge/Vue.js-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  
  <p align="center">
    <strong>A full-featured movie ticket booking platform with real-time seat selection</strong>
  </p>
  
  <p align="center">
    <a href="#-features">Features</a> •
    <a href="#-tech-stack">Tech Stack</a> •
    <a href="#-installation">Installation</a> •
    <a href="#-screenshots">Screenshots</a>
  </p>
  
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
┌─────────────────────────────────────────────────────────┐
│ Laravel 11.x │
│ ├── RESTful API │
│ ├── Sanctum Authentication (Token-based) │
│ ├── Eloquent ORM │
│ ├── MySQL Database │
│ └── File Storage (Posters/Banners) │
└─────────────────────────────────────────────────────────┘

text

### Frontend
┌─────────────────────────────────────────────────────────┐
│ Nuxt 3 (Vue 3) │
│ ├── Composition API │
│ ├── Pinia State Management │
│ ├── Tailwind CSS Styling │
│ ├── Radix Vue UI Components │
│ └── Lucide Icons │
└─────────────────────────────────────────────────────────┘

text

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
Step 2: Backend Setup (Laravel)
bash
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

Step 3: Frontend Setup (Nuxt 3)
bash
cd frontend

# Install dependencies
npm install

# Create .env file
echo "API_BASE_URL=http://localhost:8000/api" > .env

# Start development server
npm run dev
Frontend will be available at: http://localhost:3000

📸 Screenshots
<div align="center">
https://./screenshots/1.png	https://./screenshots/2.png
Home Page	Movie Details
https://./screenshots/3.png	https://./screenshots/4.png
Seat Selection	Checkout
https://./screenshots/5.png	
My Tickets	
</div>
🔑 Default Admin Credentials
Role	Email	Password
Admin	admin@legend.com	password
📁 Project Structure
text
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
🔗 API Endpoints
Method	Endpoint	Description
POST	/api/login	User login
POST	/api/register	User registration
GET	/api/movies	List movies
GET	/api/movies/{slug}/showtimes	Get movie showtimes
GET	/api/seats/availability	Check seat availability
POST	/api/bookings	Create booking
POST	/api/payments	Process payment
GET	/api/user/bookings	Get user tickets
🎯 Future Improvements
Email confirmation for bookings

Loyalty points system

Movie reviews & ratings

Food & beverage ordering

Mobile app (React Native)

🤝 Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

Fork the project

Create your feature branch (git checkout -b feature/AmazingFeature)

Commit your changes (git commit -m 'Add some AmazingFeature')

Push to the branch (git push origin feature/AmazingFeature)

Open a Pull Request

<div align="center"> Made with ❤️ by <a href="https://github.com/your-username">Your Name</a> </div> ```
What makes this README more attractive:
Badges – Colorful tech stack badges at the top

Visual separators – Clean horizontal rules and emoji icons

Feature tables – Organized feature lists with emojis

ASCII art – Simple boxes for tech stack visualization

Screenshot grid – Clean 2-column layout for images

Project structure – Tree-like visualization

API table – Clear endpoint documentation

Future improvements – Roadmap section

Contributing guide – Professional open-source section

Centered footer – Nice closing with your name

Just replace the placeholder GitHub username and add your actual screenshots to the screenshots/ folder!
