# 🧭 Travel Tales — Travel Blog Web Application

> *"Go beyond the destination. Discover the journey."*

Travel Tales is a responsive, secure travel blog built with **PHP**, **MySQL**, **HTML**, **CSS**, and **JavaScript**. It supports user registration, login, and full CRUD operations for travel stories.

## 📸 Overview & Features

- **User Authentication**
  - Secure registration with validation (username pattern, email format, password length, duplicate checks).
  - Passwords hashed with `password_hash()` (Bcrypt).
  - Login with `password_verify()` and PHP session management.
  - Logout clears session and cookies.
- **Blog Management (CRUD)**
  - Create, edit, and delete stories (authenticated users only).
  - Home page shows story cards with excerpts, search, and “My Stories” filter.
  - Single‑story view displays full content.
- **Authorization & Ownership**
  - Server‑side checks ensure only the author can edit or delete their posts.
- **Security Best Practices**
  - Prepared statements with PDO to prevent SQL injection.
  - Output escaping via `htmlspecialchars()` to mitigate XSS.
  - Environment variables loaded from `.env`; `.env` is git‑ignored.
- **Modern Responsive Design**
  - Travel‑inspired palette (Deep Navy, Ocean Teal, Warm Sand).
  - Typography using Google Fonts *Playfair Display* & *Plus Jakarta Sans*.
  - CSS Grid & Flexbox adapt across desktop, tablet, and mobile.
- **Interactive UI**
  - Client‑side password match validation.
  - Live cover‑image preview in the editor.
  - Delete‑confirmation dialogs and auto‑dismissing flash notifications.
- **Additional Enhancements**
  - View counters for each post.
  - Google Sign‑In integration.

## 📁 Project Structure

```
TravelTales/
├── config/
│   └── db.php          # Loads .env and creates PDO connection
├── css/
│   └── style.css       # Responsive stylesheet
├── includes/
│   ├── header.php      # Header, navigation, flash alerts
│   └── footer.php      # Footer and script includes
├── js/
│   └── main.js         # Delete confirmations, image preview, validation
├── .env                # Local DB credentials (git‑ignored)
├── .gitignore          # Excludes .env and temp files
├── delete.php          # Delete story with ownership check
├── editor.php          # Create / edit story
├── index.php           # Home page, filters, single story view
├── login.php           # Login page (password & Google Sign‑In)
├── logout.php          # Session termination
├── register.php        # Registration page
├── schema.sql          # Database schema and sample data
└── README.md           # Project documentation
```

## 🗄️ Database Schema (`schema.sql`)

### `users` Table
| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique user ID |
| `username` | VARCHAR(50) | NOT NULL, UNIQUE | Display name |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE | Email address |
| `password` | VARCHAR(255) | NOT NULL | Bcrypt hash |
| `role` | VARCHAR(20) | DEFAULT 'user' | User role |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Account creation |

### `blog_posts` Table
| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Post ID |
| `user_id` | INT | NOT NULL, FOREIGN KEY (`users.id`) ON DELETE CASCADE | Author |
| `title` | VARCHAR(255) | NOT NULL | Post title |
| `content` | TEXT | NOT NULL | Body |
| `image` | VARCHAR(500) | NULL | Cover image URL |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Publication time |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Last modification |
| `view_count` | INT | NOT NULL DEFAULT 0 | Number of views |


## 🔒 Security & Authorization Highlights
1. **Password Security** – Bcrypt hashing with `password_hash()`.
2. **Prepared Statements** – PDO parameters prevent SQL injection.
3. **XSS Protection** – All dynamic output escaped via `htmlspecialchars()`.
4. **Ownership Enforcement** – Server‑side checks for edit/delete actions.
5. **Credential Isolation** – Sensitive config stored in `.env` and excluded from version control.

## 📄 License & Credits
- **Project**: Travel Tales – Open‑source travel blog.
- **Technologies**: PHP 8, MySQL, HTML, CSS, JS.
- **Fonts**: Google Fonts (*Playfair Display*, *Plus Jakarta Sans*).
- **Images**: Unsplash (royalty‑free travel photography).