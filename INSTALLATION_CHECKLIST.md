# ✅ Installation Checklist - MySQL Database Integration

Gunakan checklist ini untuk memastikan semua langkah instalasi sudah dilakukan dengan benar.

---

## 📋 Pre-Installation

- [ ] **MySQL/MariaDB terinstall**
  - Laragon: MySQL sudah included
  - XAMPP: MySQL sudah included
  - Manual: Download dari [mysql.com](https://dev.mysql.com/downloads/)

- [ ] **PHP 7.4+ terinstall**
  - Cek versi: `php -v`
  - PDO extension enabled
  - PDO MySQL driver enabled

- [ ] **Apache/Nginx web server running**
  - Laragon: Auto-start
  - XAMPP: Start dari control panel
  - Test: Akses `http://localhost`

- [ ] **phpMyAdmin accessible** (opsional, untuk GUI)
  - Default: `http://localhost/phpmyadmin`

---

## 🗄️ Database Setup

### Step 1: Import SQL Schema

**Via phpMyAdmin:**
- [ ] Buka `http://localhost/phpmyadmin`
- [ ] Login (username: root, password: biasanya kosong)
- [ ] Klik tab **"Import"**
- [ ] Click **"Choose File"** → Pilih `sql/create_database.sql`
- [ ] Klik **"Go"**
- [ ] Tunggu sampai selesai (muncul "Import has been successfully finished")
- [ ] Cek database `whatsapp_logs` muncul di sidebar

**Via Command Line:**
- [ ] Buka terminal/CMD
- [ ] Masuk ke folder project: `cd C:\laragon\www\Baileys`
- [ ] Run command: `mysql -u root -p < sql/create_database.sql`
- [ ] Enter password (biasanya kosong, tekan Enter)
- [ ] Cek success: `mysql -u root -p -e "SHOW DATABASES;" | grep whatsapp_logs`

### Step 2: Verify Database Created

- [ ] Login ke MySQL: `mysql -u root -p`
- [ ] List databases: `SHOW DATABASES;`
- [ ] Cek `whatsapp_logs` ada di list
- [ ] Switch ke database: `USE whatsapp_logs;`
- [ ] List tables: `SHOW TABLES;`
- [ ] Harus muncul 4 tables:
  - [ ] activity_logs
  - [ ] log_statistics
  - [ ] message_logs
  - [ ] sessions

### Step 3: Verify Database Objects

**Check Stored Procedures:**
- [ ] `SHOW PROCEDURE STATUS WHERE Db = 'whatsapp_logs';`
- [ ] Harus ada: `GetActivityLogs`, `UpdateDailyStats`

**Check Triggers:**
- [ ] `SHOW TRIGGERS FROM whatsapp_logs;`
- [ ] Harus ada: `after_message_insert`

**Check Views:**
- [ ] `SHOW FULL TABLES WHERE Table_type = 'VIEW';`
- [ ] Harus ada: `v_daily_activity`, `v_session_performance`, `v_recent_errors`

**Check Events:**
- [ ] `SHOW EVENTS FROM whatsapp_logs;`
- [ ] Harus ada: `cleanup_old_logs`

---

## ⚙️ Configuration

### Step 1: Database Credentials

- [ ] Buka file `api/config/database.php`
- [ ] Sesuaikan credentials:
  ```php
  private $host = "localhost";        // ← Sesuaikan jika beda
  private $db_name = "whatsapp_logs"; // ← Jangan diubah
  private $username = "root";         // ← Ganti jika beda
  private $password = "";             // ← Isi jika ada password
  ```

**Default Settings:**
- Laragon: `root` / *(kosong)*
- XAMPP: `root` / *(kosong)*
- Custom install: Sesuaikan dengan setup Anda

### Step 2: Apache Configuration

- [ ] Cek file `.htaccess` ada di folder `api/`
- [ ] Pastikan `mod_rewrite` enabled (Laragon/XAMPP: default enabled)
- [ ] Pastikan `mod_headers` enabled untuk CORS

**Verify Apache Modules:**
```bash
# Check enabled modules
httpd -M | grep rewrite
httpd -M | grep headers
```

### Step 3: PHP Extensions

- [ ] Cek `php.ini` (Laragon: `C:\laragon\bin\php\php-X.X.XX\php.ini`)
- [ ] Pastikan extension enabled:
  ```ini
  extension=pdo_mysql
  extension=mysqli
  extension=mbstring
  extension=json
  ```

- [ ] Restart Apache setelah edit `php.ini`

---

## 🧪 Testing

### Step 1: Test Database Connection

- [ ] Buka browser
- [ ] Akses: `http://localhost/Baileys/api/test_connection.php`
- [ ] **Expected:** Muncul ✅ hijau "Database connection successful"
- [ ] **If error:** Cek credentials di `database.php`

### Step 2: Generate Sample Logs

- [ ] Akses: `http://localhost/Baileys/api/generate_sample_logs.php`
- [ ] **Expected:** Response JSON dengan `"inserted": 15`
- [ ] Refresh halaman: Should generate 15 new logs each time

### Step 3: Test API Endpoints

**Test GET logs:**
- [ ] Akses: `http://localhost/Baileys/api/logs/get.php?log_type=all`
- [ ] **Expected:** JSON response dengan array `logs`
- [ ] Should show sample logs yang di-generate tadi

**Test statistics:**
- [ ] Akses: `http://localhost/Baileys/api/logs/stats.php?period=today`
- [ ] **Expected:** JSON dengan `overall`, `activity_by_type`, dll
- [ ] Numbers should match sample logs count

**Test export:**
- [ ] Akses: `http://localhost/Baileys/api/logs/export.php?format=csv`
- [ ] **Expected:** Download file CSV
- [ ] Open CSV: Should contain sample logs

### Step 4: Test Frontend Integration

- [ ] Buka: `http://localhost/Baileys/public/log-activity.html`
- [ ] **Check:**
  - [ ] Statistics cards menampilkan angka
  - [ ] Log list menampilkan sample logs
  - [ ] Filter buttons berfungsi
  - [ ] Search box berfungsi
  - [ ] Export button download file
  - [ ] Clear button meminta konfirmasi

### Step 5: Test Send Message Integration

- [ ] Buka: `http://localhost/Baileys/public/send-message.html`
- [ ] Pilih "Send Text Message - Single"
- [ ] Isi nomor: `6281234567890`
- [ ] Isi pesan: `Test message`
- [ ] Klik "Send Message"
- [ ] **Expected:** Alert "Message sent successfully"
- [ ] Buka Log Activity → Harus muncul log baru

---

## 🔍 Verification

### Database Content Check

```sql
-- Total logs
SELECT COUNT(*) as total_logs FROM activity_logs;

-- Logs by type
SELECT log_type, COUNT(*) as count 
FROM activity_logs 
GROUP BY log_type;

-- Recent logs
SELECT * FROM activity_logs 
ORDER BY created_at DESC 
LIMIT 10;

-- Statistics
SELECT * FROM log_statistics 
ORDER BY stat_date DESC 
LIMIT 7;
```

### File Structure Check

- [ ] `sql/create_database.sql` - Exists
- [ ] `api/config/database.php` - Exists & configured
- [ ] `api/.htaccess` - Exists
- [ ] `api/logs/create.php` - Exists
- [ ] `api/logs/get.php` - Exists
- [ ] `api/logs/stats.php` - Exists
- [ ] `api/logs/export.php` - Exists
- [ ] `api/logs/clear.php` - Exists
- [ ] `api/test_connection.php` - Exists
- [ ] `api/generate_sample_logs.php` - Exists

---

## 🐛 Troubleshooting

### Common Issues

**Issue: "Connection failed"**
- [ ] MySQL service running: `net start mysql` (Windows) atau `systemctl start mysql` (Linux)
- [ ] Check credentials di `database.php`
- [ ] Test manual: `mysql -u root -p`

**Issue: "Database doesn't exist"**
- [ ] Import ulang `sql/create_database.sql`
- [ ] Cek nama database benar: `whatsapp_logs` (lowercase)

**Issue: "Table doesn't exist"**
- [ ] Drop database: `DROP DATABASE whatsapp_logs;`
- [ ] Import ulang SQL schema

**Issue: "Access denied for user"**
- [ ] Cek MySQL user exists: `SELECT User, Host FROM mysql.user;`
- [ ] Cek password benar
- [ ] Create user baru:
  ```sql
  CREATE USER 'root'@'localhost' IDENTIFIED BY '';
  GRANT ALL PRIVILEGES ON whatsapp_logs.* TO 'root'@'localhost';
  FLUSH PRIVILEGES;
  ```

**Issue: "CORS error in browser"**
- [ ] Cek `.htaccess` file exists di `api/`
- [ ] Check Apache `mod_headers` enabled
- [ ] Restart Apache
- [ ] Clear browser cache

**Issue: "404 Not Found for API"**
- [ ] Check Apache DocumentRoot: `httpd -S | grep DocumentRoot`
- [ ] Verify project in correct folder (Laragon: `C:\laragon\www\`)
- [ ] Check URL correct: `http://localhost/Baileys/api/...`

**Issue: "500 Internal Server Error"**
- [ ] Check PHP error log:
  - Laragon: `C:\laragon\logs\error.log`
  - XAMPP: `C:\xampp\apache\logs\error.log`
- [ ] Enable error display (development only):
  ```php
  // Add to top of API file
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```

---

## 🎯 Next Steps

Setelah semua checklist ✅:

- [ ] **Read documentation:**
  - [ ] `DATABASE_SETUP.md` - Complete setup guide
  - [ ] `api/README.md` - API documentation
  - [ ] `QUICKSTART_DATABASE.md` - Quick reference

- [ ] **Optional enhancements:**
  - [ ] Create analytics dashboard page
  - [ ] Implement user authentication
  - [ ] Setup automatic backup
  - [ ] Configure log rotation
  - [ ] Add rate limiting

- [ ] **Production deployment:**
  - [ ] Change database password
  - [ ] Enable HTTPS/SSL
  - [ ] Setup firewall rules
  - [ ] Configure monitoring
  - [ ] Setup alerts

---

## 📊 Success Criteria

✅ **Installation successful jika:**

1. **Database:**
   - [ ] 4 tables created
   - [ ] 2 stored procedures
   - [ ] 1 trigger
   - [ ] 3 views
   - [ ] 1 event scheduler

2. **API:**
   - [ ] Test connection = ✅ green
   - [ ] 5 endpoints responding
   - [ ] Sample logs generated

3. **Frontend:**
   - [ ] Log activity page showing logs
   - [ ] Statistics displaying correctly
   - [ ] Filters working
   - [ ] Export functioning

4. **Integration:**
   - [ ] Send message creates log
   - [ ] Log saved to database
   - [ ] Statistics updated

---

## 🎉 Installation Complete!

Jika semua checklist ✅, **Selamat!** Database integration Anda sudah siap digunakan.

**Questions?**
- Review documentation di folder `docs/`
- Check error logs
- Test API via Postman/Insomnia

---

Last updated: 2025-01-23
Version: 1.0.0
