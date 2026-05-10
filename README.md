# <p align="center"><img src="assets/img/logo.png" alt="Traveloop Logo" width="140"><br>Traveloop</p>

<p align="center">
  <strong>The Ultimate Hackathon Management & Travel SaaS Platform.</strong><br>
  <em>A premium, full-stack solution for modern trip planning and financial tracking.</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2-blue.svg?style=for-the-badge&logo=php" alt="PHP Version">
  <img src="https://img.shields.io/badge/MySQL-8.0-orange.svg?style=for-the-badge&logo=mysql" alt="MySQL Version">
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED.svg?style=for-the-badge&logo=docker" alt="Docker">

</p>

---

## 🚀 Vision

**Traveloop** was engineered for the 2026 Hackathon to solve the complexities of group and solo travel. It combines high-performance backend architecture with a cutting-edge  to deliver a seamless user experience. From intelligent financial analytics to dynamic itinerary sequencing, Traveloop is the future of travel management.

---

## 💎 Features & Functionality

### 🗺️ Intelligent Itinerary Planning
- **Dynamic Timelines**: Build day-by-day itineraries with precise activity sequencing.
- **Activity Categorization**: Organize plans into *Transport, Stay, Activities, and Meals*.
- **Status Tracking**: Monitor the progress of planned activities (Planned, Completed, Cancelled).
- **City Integration**: Seamlessly add stops with automated city discovery.

### 📊 Advanced Financial Analytics
- **Live Budget Tracking**: Real-time comparison between estimated and actual expenses.
- **Category Breakdown**: Automated expense analysis across different travel categories.
- **Daily Spend Metrics**: Intelligent calculation of average daily costs and trip duration.
- **Smart Insights**: Automatically identifies the most expensive stops in your journey.

### 🎒 Utility & Productivity Tools
- **Dynamic Packing Checklists**: Category-aware checklists (Clothing, Electronics, Documents, etc.) with persistent state.
- **Persistent Trip Notes**: Rich text-ready notes for capturing important trip details and memories.
- **City Discovery Engine**: Searchable database of world cities with integrated cost-of-living indices ($, $$, $$$).

### 👤 Premium User Experience
- **Profile Customization**: Full control over user data, including name and email management.
- **Avatar System**: Secure image upload system for custom profile avatars (Max 2MB).
- **Secure Auth**: Robust login/registration system with BCrypt password hashing.
- **Responsive Mastery**: Fluid layouts optimized for desktop, tablet, and mobile browsers.

---

## 🛠️ Technical Excellence

### 🏗️ Architecture
- **Modular MVC-Inspired Design**: Clean separation of concerns between Models, Views, and Controllers.
- **RESTful API Core**: A structured API layer powering all frontend interactions via JSON.
- **Clean URL Routing**: Optimized request handling for a smooth, app-like navigation feel.

### 🔒 Security First
- **CSRF Protection**: Global cross-site request forgery validation on all mutating requests (Header & Body).
- **Data Integrity**: Transactional database operations to ensure consistency during complex deletions.
- **Sanitized Inputs**: Comprehensive input validation and XSS prevention.



## 📦 Getting Started

### Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Deployment in Seconds
1. **Clone & Enter**
   ```bash
   git clone https://github.com/yourusername/traveloop.git
   cd traveloop
   ```

2. **Launch Services**
   ```bash
   docker-compose up -d --build
   ```

3. **Explore**
   Navigate to [http://localhost:8080](http://localhost:8080)

---

## 🗃️ Database Environment
The system leverages a high-availability MySQL 8.0 configuration, automatically initialized via `config/setup.sql`.

| Variable | Value |
| :--- | :--- |
| **DB Host** | `db` |
| **Database** | `traveloop` |
| **DB User** | `traveloop` |
| **DB Password** | `secret` |

---

## 📂 System Architecture

```text
├── api/routes/      # High-performance RESTful API endpoints
├── assets/          # design system & JS logic
├── config/          # Environment & Database configurations
├── includes/        # Shared components & Security middleware
├── pages/           # Dynamic frontend templates
├── src/Models/      # Business logic & Database Abstraction Layer
├── Dockerfile       # Optimized PHP-Apache container config
└── docker-compose.yml # Full-stack service orchestration
```

---

<p align="center">
  <strong>Built with passion for the 2026 Hackathon.</strong><br>
  Traveloop — Explore without limits.
</p>
