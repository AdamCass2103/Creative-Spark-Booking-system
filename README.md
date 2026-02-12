# Booking System Prototype

A simple booking system prototype with user registration, login, and admin panel.

## Features
- User signup with email and password
- Three tick boxes:
  1. Returning member
  2. Needs training
  3. Terms and services acceptance
- User dashboard to view preferences
- Admin panel with user management
- Filter users by:
  - Needs training
  - No training needed
  - New users
  - Pending approval
- Update training status (pending/approved/rejected/completed)

## Setup Instructions

### 1. Install XAMPP
- Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
- Start Apache and MySQL from the XAMPP Control Panel

### 2. Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `booking_system`
3. Import the SQL file from `database/booking_system.sql`

### 3. Project Setup
1. Place the project folder in `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac)
2. Rename the folder to `booking-system`
3. Open `includes/config.php` and update database credentials if needed

### 4. Access the Application
1. Open your browser
2. Go to: `http://localhost/booking-system/`
3. Sign up for a new account

### 5. Admin Access
- Use email: `admin@booking.com`
- Password: `admin123`

## File Structure
- `index.php` - Signup page
- `login.php` - Login page
- `dashboard.php` - User dashboard
- `admin/index.php` - Admin panel
- `css/style.css` - Stylesheet
- `includes/` - Configuration files
- `database/` - SQL file

## Requirements
- XAMPP (Apache, MySQL, PHP)
- Modern web browser

## Security Notes
This is a prototype for demonstration purposes. For production use:
- Use stronger password hashing
- Implement CSRF protection
- Add input validation
- Use HTTPS
- Implement proper session management