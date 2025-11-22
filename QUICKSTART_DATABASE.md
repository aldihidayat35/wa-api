# 🚀 Quick Start - Database Integration

Panduan cepat untuk memulai menggunakan sistem logging dengan MySQL database.

---

## ⚡ 3 Langkah Mudah

### 1️⃣ Import Database (1 menit)

**Via phpMyAdmin:**
1. Buka `http://localhost/phpmyadmin`
2. Klik tab **"Import"**
3. Pilih file `sql/create_database.sql`
4. Klik **"Go"**

**Via Command Line:**
```bash
mysql -u root -p < sql/create_database.sql
```

### 2️⃣ Test Koneksi (30 detik)

Buka browser dan akses:
```
http://localhost/Baileys/api/test_connection.php
```

Jika muncul ✅ hijau = **SUKSES!**

Jika ada error merah ❌:
- Cek MySQL service berjalan
- Sesuaikan username/password di `api/config/database.php`

### 3️⃣ Generate Sample Data (Opsional)

Untuk testing, generate sample logs:
```
http://localhost/Baileys/api/generate_sample_logs.php
```

Akan membuat 15 sample logs berbagai tipe.

---

## ✅ Verifikasi

1. **Buka halaman log:**
   ```
   http://localhost/Baileys/public/log-activity.html
   ```

2. **Cek log muncul di tabel**

3. **Test filter (All, Message, Session, Error, dll)**

4. **Test export CSV/JSON**

5. **Test search**

---

## 🎯 Test Send Message

1. Buka: `http://localhost/Baileys/public/send-message.html`

2. Pilih tipe pesan (Text atau Image)

3. Kirim test message

4. Buka **Log Activity** - harus muncul log baru

---

## 📊 API Endpoints

Base URL: `http://localhost/Baileys/api/logs/`

### Create Log
```javascript
fetch('http://localhost/Baileys/api/logs/create.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    log_type: 'message',
    title: 'Test Log',
    description: 'Testing API endpoint',
    session_id: 'session1'
  })
});
```

### Get Logs
```javascript
fetch('http://localhost/Baileys/api/logs/get.php?log_type=all&limit=50')
  .then(res => res.json())
  .then(data => console.log(data));
```

### Get Statistics
```javascript
fetch('http://localhost/Baileys/api/logs/stats.php?period=today')
  .then(res => res.json())
  .then(data => console.log(data));
```

---

## 🛠️ Konfigurasi (Jika diperlukan)

Edit `api/config/database.php`:

```php
private $host = "localhost";
private $db_name = "whatsapp_logs";
private $username = "root";        // ← Ganti jika berbeda
private $password = "";            // ← Ganti jika ada password
```

Default untuk **Laragon**: username=`root`, password=*(kosong)*

---

## 📝 Cara Kerja Sistem

### Hybrid Approach (LocalStorage + Database):

1. **Log baru dibuat** → Simpan ke LocalStorage (cepat)
2. **Kirim ke API** → Simpan ke Database (persistent)
3. **Load halaman** → Ambil dari LocalStorage (instan) + Merge dari Database
4. **Export/Clear** → Langsung dari Database

### Keuntungan:
- ⚡ **Fast**: LocalStorage untuk quick access
- 💾 **Persistent**: Database untuk long-term storage
- 🔄 **Sync**: Auto-merge mencegah duplikasi
- 📊 **Analytics**: Database query untuk statistik

---

## 🔍 Troubleshooting

### "Connection failed"
```bash
# Cek MySQL berjalan
net start mysql

# Test connection
mysql -u root -p
```

### "Table doesn't exist"
- Import ulang: `sql/create_database.sql`
- Cek database name = `whatsapp_logs`

### "Access denied"
- Cek username/password di `api/config/database.php`
- Test manual: `mysql -u root -p`

### CORS Error
- Cek file `.htaccess` ada di folder `api/`
- Restart Apache

---

## 📚 Dokumentasi Lengkap

Baca `DATABASE_SETUP.md` untuk:
- Detail database schema
- API endpoint documentation
- Security best practices
- Maintenance & backup
- Advanced features

---

## 🎉 Selesai!

Sistem logging Anda sudah siap!

**Next Steps:**
- Integrasikan dengan WhatsApp bot
- Setup auto-backup
- Create analytics dashboard
- Deploy to production

**Need Help?**
- Check error di browser console
- Check PHP error log
- Review `DATABASE_SETUP.md`

---

Dibuat dengan ❤️ untuk WhatsApp Multi-Session Bot
