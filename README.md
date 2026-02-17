# 🎓 MAZAR — Educational Platform
## Complete Deployment Guide for InfinityFree

---

## 📁 Project Structure

```
mazar/
├── index.php              ← Landing page (multi-lang)
├── config.php             ← ⚙️  DB credentials & app constants
├── login.php              ← Student/Admin login
├── register.php           ← Student registration
├── logout.php             ← Session destroy
├── database.sql           ← 🔑 Full MySQL schema + seed data
├── .htaccess              ← Security + compression rules
│
├── /includes/
│   ├── db.php             ← PDO connection (singleton)
│   ├── functions.php      ← XP, translations, helpers
│   ├── auth_check.php     ← Protect student pages
│   └── admin_auth.php     ← Protect admin pages
│
├── /lang/
│   ├── ar.php             ← Arabic translations (RTL)
│   ├── fr.php             ← French translations
│   └── en.php             ← English translations
│
├── /student/
│   └── dashboard.php      ← Student dashboard (XP, Lessons, Leaderboard)
│
├── /admin/
│   ├── _layout.php        ← Admin sidebar layout
│   ├── _layout_end.php    ← Admin footer layout
│   ├── dashboard.php      ← Admin stats & activity
│   ├── manage_lessons.php ← CRUD lessons + live link preview
│   └── manage_users.php   ← Users management + XP control
│
├── /ajax/
│   └── complete_lesson.php← XP endpoint (secure POST)
│
├── /assets/
│   ├── css/
│   │   └── xp-animations.css ← Animations, toasts, shimmer
│   └── js/
│       └── xp-system.js   ← XP logic, confetti, count-up
│
└── /uploads/              ← Local uploads (thumbnails)
    └── .htaccess          ← Blocks PHP execution in uploads
```

---

## 🚀 Step-by-Step Deployment on InfinityFree

### Step 1: Create Database
1. Login to InfinityFree Control Panel
2. Go to **MySQL Databases** → Create a new database
3. Note your: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
4. Open **phpMyAdmin** → Select your database → Click **Import**
5. Import `database.sql` ✅

### Step 2: Configure `config.php`
Edit `config.php` and replace:
```php
define('DB_HOST', 'sql123.epizy.com');  // Your InfinityFree host
define('DB_NAME', 'epiz_xxx_mazar');    // Your DB name
define('DB_USER', 'epiz_xxx');          // Your DB user
define('DB_PASS', 'your_password');     // Your DB password
define('BASE_URL', 'https://yourdomain.epizy.com');
```

### Step 3: Upload Files
Use **FileZilla** or the InfinityFree File Manager:
1. Upload ALL files to the `htdocs/` folder
2. Keep the folder structure exactly as shown above
3. Make sure `.htaccess` is uploaded (it may be hidden — enable "Show hidden files" in FileZilla)

### Step 4: Set File Permissions
- Folders: `755`
- PHP files: `644`
- `uploads/` folder: `755`

### Step 5: Test Your Site
- Landing page: `https://yourdomain.com`
- Login page: `https://yourdomain.com/login.php`
- Admin: `https://yourdomain.com/admin/dashboard.php`

**Default Admin Credentials:**
- Email: `admin@mazar.ma`
- Password: `Admin@1234`
> ⚠️ Change this immediately after first login via manage_users.php!

---

## 🎮 XP System Rules

| Action           | XP Earned |
|------------------|-----------|
| Complete Lesson  | +10 XP    |
| Pass a Quiz      | +50 XP    |

| Level | XP Required |
|-------|-------------|
| 1     | 0           |
| 2     | 100         |
| 3     | 300         |
| 4     | 600         |
| 5     | 1,000       |
| 6     | 1,500       |
| 7     | 2,200       |
| 8     | 3,000       |
| 9     | 4,000       |
| 10    | 5,500       |

---

## 🌍 Multi-Language Support

| Lang | File          | Direction |
|------|---------------|-----------|
| AR   | lang/ar.php   | RTL       |
| FR   | lang/fr.php   | LTR       |
| EN   | lang/en.php   | LTR       |

Switch via URL: `?lang=ar` / `?lang=fr` / `?lang=en`

---

## 📹 Media Hosting

- **Videos** → YouTube links only (no storage on server)
- **PDFs / Books** → MediaFire direct download links
- **Thumbnails** → Auto-extracted from YouTube OR MediaFire URL

---

## 🛠️ Adding Content (Admin Guide)

1. Login as admin
2. Go to **Manage Lessons** → **Add Lesson**
3. Fill in:
   - Title in FR (required), AR, EN (optional)
   - Select Grade Level → Subject auto-loads
   - Content Type: Video / PDF / Book
   - Paste URL — Live preview appears instantly
4. Click **Save**

---

## 🔒 Security Features

- ✅ CSRF protection on all forms
- ✅ Password hashing with bcrypt (cost=12)
- ✅ Session regeneration on login
- ✅ Role-based access control (student / admin / super_admin)
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS prevention (htmlspecialchars everywhere)
- ✅ Directory listing disabled
- ✅ includes/ and lang/ blocked from direct access
- ✅ PHP execution blocked in uploads/

---

## 📦 External Libraries (CDN — No installation needed)

| Library        | Version | Purpose              |
|----------------|---------|----------------------|
| Tailwind CSS   | Latest  | Styling              |
| Alpine.js      | 3.x     | Reactive UI          |
| Lucide Icons   | Latest  | Icons                |
| Animate.css    | 4.x     | Entry animations     |
| DataTables     | 1.10.21 | Admin tables         |
| canvas-confetti| 1.9.2   | Level-up celebration |
| jQuery         | 3.7.1   | DataTables dependency|

---

## 📧 Support

Platform: **MAZAR** — Built for Moroccan Students 🇲🇦
Tech: PHP 7.4+ / MySQL 5.7+ / InfinityFree Compatible

---

*© 2024 MAZAR Educational Platform*
