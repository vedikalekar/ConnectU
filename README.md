# SocialMini — Setup Guide

A beginner-friendly full-stack social media platform built with:
**HTML · CSS · JavaScript · PHP · MySQL**

---

## 📁 Project Structure

```
socialmini/
├── index.php            ← Landing Page
├── register.php         ← Register Page
├── login.php            ← Login Page
├── logout.php           ← Logout
├── dashboard.php        ← Home Feed (main page)
├── create_post.php      ← Create Post
├── post_detail.php      ← Single Post + Comments
├── edit_post.php        ← Edit Post
├── delete_post.php      ← Delete Post
├── delete_comment.php   ← Delete Comment
├── profile.php          ← User Profile
├── edit_profile.php     ← Edit Profile
├── followers.php        ← Followers List
├── following.php        ← Following List
├── search.php           ← Search Users
├── notifications.php    ← Notifications
├── like.php             ← AJAX: Like Handler
├── comment.php          ← AJAX: Comment Handler
├── follow.php           ← AJAX: Follow Handler
├── database.sql         ← Database Schema + Sample Data
│
├── css/
│   └── style.css        ← Main Stylesheet
│
├── js/
│   └── main.js          ← Main JavaScript
│
├── includes/
│   ├── db.php           ← Database Connection
│   ├── auth.php         ← Session Helpers
│   └── nav.php          ← Shared Sidebar Navigation
│
└── uploads/
    ├── profiles/        ← Profile pictures (auto-created)
    └── posts/           ← Post images (auto-created)
```

---

## ⚙️ Setup Instructions (Step by Step)

### Step 1: Install XAMPP (or WAMP)
Download from: https://www.apachefriends.org/
- Install XAMPP
- Start **Apache** and **MySQL** from the XAMPP Control Panel

### Step 2: Copy project files
Copy the entire `socialmini/` folder into:
```
C:\xampp\htdocs\socialmini\
```
(On Mac: `/Applications/XAMPP/htdocs/socialmini/`)

### Step 3: Create the database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **Import** tab
3. Click **Choose File** and select `database.sql`
4. Click **Go** — this creates the database and tables

### Step 4: Configure database connection
Open `includes/db.php` and update if needed:
```php
$host     = "localhost";   // Usually localhost
$dbname   = "socialmini";  // Leave as is
$username = "root";        // Your MySQL username (XAMPP default: root)
$password = "";            // Your MySQL password (XAMPP default: empty)
```

### Step 5: Run the project
Open your browser and go to:
```
http://localhost/socialmini/
```

---

## 🗄️ Database Tables (5 tables)

| Table     | Purpose                          |
|-----------|----------------------------------|
| users     | Stores all user accounts         |
| posts     | Stores all posts                 |
| comments  | Stores comments on posts         |
| likes     | Stores who liked which post      |
| follows   | Stores follower relationships    |

---

## 🔐 Demo Accounts

After importing `database.sql`, you can log in with:

| Username | Password    |
|----------|-------------|
| alice    | password123 |
| bob      | password123 |
| carol    | password123 |

---

## 📄 Pages Overview

| Page               | File               | Description                       |
|--------------------|--------------------|-----------------------------------|
| Landing            | index.php          | Public homepage with features     |
| Register           | register.php       | Create a new account              |
| Login              | login.php          | Log in with email or username     |
| Dashboard          | dashboard.php      | News feed from followed users     |
| Create Post        | create_post.php    | Write & upload posts              |
| Post Detail        | post_detail.php    | Full post with all comments       |
| Edit Post          | edit_post.php      | Edit your own post                |
| Profile            | profile.php        | View any user's profile           |
| Edit Profile       | edit_profile.php   | Update bio, picture, password     |
| Followers          | followers.php      | See who follows a user            |
| Following          | following.php      | See who a user follows            |
| Search             | search.php         | Find users by name/username       |
| Notifications      | notifications.php  | Likes, comments, follows alerts   |

---

## 🛠️ Technologies Used

- **HTML5** — Page structure and content
- **CSS3** — Styling, layout (Flexbox/Grid), animations
- **JavaScript** — AJAX, live search, interactive buttons
- **PHP 8+** — Server-side logic, form handling, sessions
- **MySQL** — Database for users, posts, comments, likes, follows

---

## 💡 Beginner Tips

- All PHP files start by requiring `includes/auth.php` and `includes/db.php`
- `requireLogin()` protects pages that need authentication
- `safe()` function prevents XSS (security) — always use it when displaying user data
- Like/Comment/Follow use **AJAX** (JavaScript `fetch`) so the page doesn't reload
- Passwords are hashed with `password_hash()` — never store plain text!
- Images are stored in `uploads/` folder with unique names

---

## 🚨 Troubleshooting

**Database error?**
→ Make sure XAMPP MySQL is running and you imported `database.sql`

**Images not showing?**
→ Make sure `uploads/profiles/` and `uploads/posts/` folders exist and are writable

**Blank page?**
→ Enable PHP error display: add `ini_set('display_errors', 1);` at top of the file

**Permission denied uploading?**
→ Right-click `uploads/` folder → Properties → allow write permissions
