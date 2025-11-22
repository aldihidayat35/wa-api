# 🗄️ Database & API Setup Guide

## 📋 Panduan Instalasi MySQL Database

### 1️⃣ Import Database Schema

#### Menggunakan phpMyAdmin:
1. Buka **phpMyAdmin** di browser: `http://localhost/phpmyadmin`
2. Klik tab **"Import"**
3. Klik **"Choose File"** dan pilih file `sql/create_database.sql`
4. Klik **"Go"** untuk mengimport
5. Database `whatsapp_logs` akan otomatis dibuat

#### Menggunakan Command Line:
```bash
# Masuk ke direktori project
cd C:\laragon\www\Baileys

# Import SQL file
mysql -u root -p < sql/create_database.sql

# Atau jika ada password
mysql -u root -pYOUR_PASSWORD < sql/create_database.sql
```

---

### 2️⃣ Konfigurasi Database Connection

Edit file `api/config/database.php`:

```php
private $host = "localhost";
private $db_name = "whatsapp_logs";
private $username = "root";        // Sesuaikan dengan username MySQL Anda
private $password = "";            // Sesuaikan dengan password MySQL Anda
```

**Untuk Laragon (Default):**
- Username: `root`
- Password: *(kosong)*

**Untuk XAMPP/WAMP:**
- Username: `root`
- Password: *(kosong)* atau cek konfigurasi Anda

---

### 3️⃣ Test Koneksi Database

Buat file test `api/test_connection.php`:

```php
<?php
require_once 'config/database.php';

$result = Database::testConnection();

if ($result['success']) {
    echo "✅ " . $result['message'];
} else {
    echo "❌ " . $result['message'];
}
?>
```

Akses: `http://localhost/Baileys/api/test_connection.php`

Jika berhasil, akan muncul: **✅ Database connection successful**

---

## 🚀 Struktur Database

### Tables:

#### 1. **activity_logs** (Tabel Utama)
Menyimpan semua aktivitas sistem:
- `id` - Primary key
- `log_type` - message, session, error, system, api
- `title` - Judul aktivitas
- `description` - Deskripsi detail
- `session_id` - ID session WhatsApp
- `phone_number` - Nomor telepon terkait
- `metadata` - Data tambahan (JSON format)
- `ip_address` - IP address client
- `user_agent` - Browser/client info
- `created_at` - Waktu aktivitas

#### 2. **log_statistics** (Statistik Harian)
Menyimpan ringkasan statistik per hari

#### 3. **sessions** (Informasi Session)
Menyimpan data semua session WhatsApp

#### 4. **message_logs** (Detail Pesan)
Menyimpan tracking detail setiap pesan

---

## 📡 API Endpoints

### Base URL: `/api/logs/`

### 1. **Create Log** (Simpan Log Baru)
```http
POST /api/logs/create.php
Content-Type: application/json

{
  "log_type": "message",
  "title": "Pesan Terkirim",
  "description": "Pesan berhasil dikirim ke 628123456789",
  "session_id": "session1",
  "phone_number": "628123456789",
  "metadata": {
    "messageId": "msg123",
    "recipient": "628123456789"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Log created successfully",
  "data": {
    "id": 1,
    "log_type": "message",
    "title": "Pesan Terkirim"
  }
}
```

---

### 2. **Get Logs** (Ambil Log dengan Filter)
```http
GET /api/logs/get.php?log_type=message&limit=50&offset=0
```

**Query Parameters:**
- `log_type` - Filter berdasarkan tipe (message, session, error, system, api, all)
- `session_id` - Filter berdasarkan session
- `search` - Pencarian di title & description
- `limit` - Jumlah data (default: 100)
- `offset` - Offset untuk pagination (default: 0)
- `date_from` - Filter tanggal dari (format: YYYY-MM-DD)
- `date_to` - Filter tanggal sampai (format: YYYY-MM-DD)

**Response:**
```json
{
  "success": true,
  "message": "Logs retrieved successfully",
  "data": {
    "logs": [...],
    "pagination": {
      "total": 150,
      "limit": 50,
      "offset": 0,
      "has_more": true
    }
  }
}
```

---

### 3. **Get Statistics** (Ambil Statistik)
```http
GET /api/logs/stats.php?period=today
```

**Query Parameters:**
- `period` - today, week, month, all

**Response:**
```json
{
  "success": true,
  "data": {
    "period": "today",
    "overall": {
      "total_activities": 125,
      "total_messages": 85,
      "total_errors": 5,
      "active_sessions": 3
    },
    "activity_by_type": [...],
    "daily_activity": [...],
    "top_sessions": [...]
  }
}
```

---

### 4. **Export Logs** (Export ke CSV/JSON)
```http
GET /api/logs/export.php?format=csv&log_type=all
```

**Query Parameters:**
- `format` - csv atau json
- `log_type` - Filter tipe log
- `date_from` - Tanggal dari
- `date_to` - Tanggal sampai

**Response:** Download file CSV atau JSON

---

### 5. **Clear Logs** (Hapus Log)
```http
POST /api/logs/clear.php
Content-Type: application/json

{
  "action": "old",
  "days": 90
}
```

**Actions:**
- `old` - Hapus log lama (parameter: days)
- `all` - Hapus semua log
- `by_type` - Hapus berdasarkan tipe (parameter: log_type)

---

## 🔧 Konfigurasi Apache/Nginx

### Apache (Laragon/XAMPP):
File `.htaccess` sudah disediakan di folder `api/`

### Nginx:
Tambahkan di `nginx.conf`:
```nginx
location /api/ {
    add_header Access-Control-Allow-Origin *;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS";
    add_header Access-Control-Allow-Headers "Content-Type, Authorization";
    
    if ($request_method = 'OPTIONS') {
        return 200;
    }
    
    try_files $uri $uri/ =404;
}
```

---

## 🛠️ Maintenance

### Auto-Delete Old Logs
Database schema sudah include **Event** untuk auto-delete log > 90 hari.

Cek event aktif:
```sql
SHOW EVENTS;
```

Enable event scheduler:
```sql
SET GLOBAL event_scheduler = ON;
```

### Manual Cleanup
```sql
-- Hapus log > 90 hari
DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Hapus log > 1 tahun
DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### Backup Database
```bash
# Daily backup via cron
mysqldump -u root -p whatsapp_logs > backup/whatsapp_logs_$(date +%Y%m%d).sql

# Restore backup
mysql -u root -p whatsapp_logs < backup/whatsapp_logs_20250123.sql
```

---

## 📊 Fitur Database

### ✅ Yang Sudah Tersedia:

1. **Auto-increment ID** - Primary key otomatis
2. **JSON Metadata** - Simpan data kompleks
3. **Indexes** - Query cepat & optimal
4. **Full-text Search** - Pencarian description
5. **Views** - Ready-made reporting queries
6. **Triggers** - Auto-update session stats
7. **Stored Procedures** - Query kompleks
8. **Auto-cleanup Event** - Hapus log lama otomatis
9. **UTF-8 Support** - Emoji & karakter khusus

### 🎯 Performance Tips:

- Gunakan pagination (limit & offset)
- Filter by date range untuk data besar
- Index sudah optimal, jangan hapus!
- Pertimbangkan partitioning jika data > 1 juta

---

## 🔐 Security

### Best Practices:
1. **Ganti password MySQL default**
2. **Buat user khusus untuk aplikasi:**
   ```sql
   CREATE USER 'whatsapp_user'@'localhost' IDENTIFIED BY 'strong_password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON whatsapp_logs.* TO 'whatsapp_user'@'localhost';
   FLUSH PRIVILEGES;
   ```
3. **Update `api/config/database.php` dengan user baru**
4. **Jangan expose phpMyAdmin ke internet**
5. **Enable SSL untuk production**

---

## ❓ Troubleshooting

### Error: "Connection failed"
- Cek MySQL service running: `net start mysql`
- Cek username & password di `config/database.php`
- Cek database `whatsapp_logs` sudah dibuat

### Error: "Table doesn't exist"
- Import ulang `sql/create_database.sql`
- Cek database name benar

### Error: "Access denied"
- Cek user privileges: `SHOW GRANTS FOR 'root'@'localhost';`
- Cek password MySQL

### CORS Error di Browser
- Cek `.htaccess` di folder `api/`
- Cek Apache modules: `mod_headers` enabled
- Restart Apache/Nginx

---

## 📞 Support

Jika ada masalah:
1. Cek error di PHP error log
2. Cek MySQL error log
3. Test API via Postman/Insomnia
4. Cek browser console untuk error frontend

---

## ✅ Checklist Instalasi

- [ ] Import `sql/create_database.sql`
- [ ] Edit `api/config/database.php`
- [ ] Test connection via `api/test_connection.php`
- [ ] Cek `.htaccess` di folder `api/`
- [ ] Restart Apache/Nginx
- [ ] Test API endpoint via browser/Postman
- [ ] Buka halaman log-activity.html
- [ ] Kirim test message, cek log tersimpan

**Selamat! Database Anda sudah siap! 🎉**
