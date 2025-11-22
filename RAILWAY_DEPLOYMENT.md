# Deploy WhatsApp API ke Railway.app

Panduan lengkap deploy aplikasi WhatsApp API (Baileys) ke Railway.app dengan **GRATIS**.

## 🚀 Langkah-Langkah Deploy

### 1️⃣ Persiapan Repository GitHub

**A. Inisialisasi Git (jika belum)**
```bash
cd c:\laragon\www\Baileys
git init
git add .
git commit -m "Initial commit: WhatsApp API with Baileys"
```

**B. Create Repository di GitHub**
1. Buka https://github.com/new
2. Nama repository: `wa-api` (atau nama lain)
3. **JANGAN** centang "Add README" atau "Add .gitignore"
4. Klik **Create repository**

**C. Push ke GitHub**
```bash
git remote add origin https://github.com/aldihidayat35/wa-api.git
git branch -M main
git push -u origin main
```

---

### 2️⃣ Deploy ke Railway

**A. Sign Up Railway**
1. Buka https://railway.app
2. Klik **"Start a New Project"** atau **"Login"**
3. Login dengan akun **GitHub** (recommended)
4. Authorize Railway untuk akses GitHub

**B. Create New Project**
1. Setelah login, klik **"New Project"**
2. Pilih **"Deploy from GitHub repo"**
3. Pilih repository **aldihidayat35/wa-api**
4. Railway akan otomatis detect dan mulai build

**C. Tunggu Deployment**
- Railway akan:
  - Install dependencies (`npm install`)
  - Build TypeScript (`npm run build`)
  - Start server (`npm run web`)
- Proses ~3-5 menit
- Lihat logs real-time di Railway dashboard

---

### 3️⃣ Setup Database MySQL

**A. Add MySQL Database**
1. Di Railway dashboard, klik **"+ New"**
2. Pilih **"Database"** → **"Add MySQL"**
3. Railway akan provision MySQL database

**B. Import Database Structure**

Railway tidak punya phpMyAdmin, gunakan MySQL client:

**Opsi 1: MySQL Workbench**
1. Download [MySQL Workbench](https://dev.mysql.com/downloads/workbench/)
2. Di Railway, klik MySQL service → **"Connect"**
3. Copy connection details:
   - Host: `containers-us-west-xxx.railway.app`
   - Port: `6379` (biasanya)
   - User: `root`
   - Password: `xxx`
   - Database: `railway`
4. Connect di Workbench
5. Import file `database.sql`

**Opsi 2: Command Line**
```bash
# Download database credentials dari Railway
# Klik MySQL → Variables → Copy MYSQL_URL

# Import via mysql client
mysql -h containers-us-west-xxx.railway.app -P 6379 -u root -p railway < database.sql
```

**C. Buat Tabel (Manual Query)**

Jika belum punya file SQL, jalankan query ini:

```sql
CREATE DATABASE IF NOT EXISTS railway;
USE railway;

CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100),
    log_type VARCHAR(50),
    title VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_type (log_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### 4️⃣ Konfigurasi Environment Variables

**A. Set Database Connection**
1. Di Railway, klik **Node.js service** Anda
2. Klik tab **"Variables"**
3. Tambahkan variable berikut:

**Jika menggunakan PHP API (optional):**
```
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_USER=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}
DB_NAME=${{MySQL.MYSQL_DATABASE}}
```

**Untuk Node.js (jika perlu):**
```
DATABASE_URL=${{MySQL.DATABASE_URL}}
PORT=3000
NODE_ENV=production
```

Railway otomatis inject MySQL variables, jadi bisa langsung pakai `${{MySQL.VARIABLE_NAME}}`.

---

### 5️⃣ Custom Domain (waapi.demoj35.site)

**A. Generate Railway Domain (Default)**
1. Di Railway service, klik tab **"Settings"**
2. Scroll ke **"Domains"**
3. Klik **"Generate Domain"**
4. Anda dapat domain: `wa-api-production-xxxx.up.railway.app`

**B. Add Custom Domain**
1. Masih di tab **"Settings"** → **"Domains"**
2. Klik **"Custom Domain"**
3. Masukkan: `waapi.demoj35.site`
4. Railway akan berikan **CNAME record**:
   ```
   wa-api-production-xxxx.up.railway.app
   ```

**C. Update DNS di RumahWeb**
1. Login ke panel RumahWeb
2. Masuk **Domain Management**
3. Pilih domain `demoj35.site`
4. Klik **"Manage DNS"**
5. Tambahkan **CNAME Record**:
   ```
   Type: CNAME
   Name: waapi
   Value: wa-api-production-xxxx.up.railway.app
   TTL: 3600
   ```
6. **Save**
7. Tunggu propagasi DNS (5-60 menit)

**D. SSL Otomatis**
- Railway otomatis provision SSL certificate
- HTTPS akan aktif setelah DNS propagate
- Akses: `https://waapi.demoj35.site`

---

### 6️⃣ Verifikasi & Testing

**A. Cek Deployment Status**
```
✅ Build success
✅ Deploy success
✅ Service running
```

**B. Test URL**
1. Buka Railway domain: `https://wa-api-production-xxxx.up.railway.app`
2. Atau custom domain: `https://waapi.demoj35.site`
3. Harusnya muncul dashboard WhatsApp API

**C. Cek Logs**
- Di Railway dashboard → **"Deployments"** → klik latest deploy
- Lihat logs untuk error
- Pastikan tidak ada error database connection

---

### 7️⃣ Setup PHP API (Opsional)

Railway support Node.js natively, tapi **TIDAK support PHP** secara default.

**Solusi 1: Pindahkan Logic PHP ke Node.js**
Refactor `api/logs/*.php` jadi REST API di Node.js (recommended)

**Solusi 2: Deploy PHP Terpisah**
- Deploy PHP API ke hosting lain (shared hosting RumahWeb bisa)
- Node.js di Railway, PHP API di RumahWeb
- Update URL di frontend: `http://yourdomain.com/api/logs/...`

**Solusi 3: Docker Multi-Service (Advanced)**
Buat Dockerfile yang run PHP-FPM + Node.js, tapi kompleks.

**Rekomendasi:** Gunakan **Solusi 1** - refactor ke Node.js

---

## 📊 Monitoring & Maintenance

### Resource Usage (Free Tier)
- **500 execution hours/month**
- **512 MB RAM**
- **1 GB disk**
- **100 GB bandwidth**

Cukup untuk 1-2 WhatsApp sessions dengan traffic rendah-medium.

### Commands

**Redeploy:**
- Push ke GitHub → auto redeploy
- Atau klik **"Redeploy"** di Railway dashboard

**View Logs:**
```
Railway dashboard → Deployments → Latest → View Logs
```

**Restart Service:**
```
Settings → Restart
```

**Scale Up (Paid):**
- Upgrade ke **Hobby Plan** ($5/month)
- Unlimited execution hours
- More resources

---

## 🔧 Update Aplikasi

Setelah deploy, jika ada perubahan code:

```bash
# 1. Commit changes
git add .
git commit -m "Update feature X"

# 2. Push ke GitHub
git push origin main

# 3. Railway auto-deploy (tunggu 2-3 menit)
```

Railway otomatis rebuild & redeploy setiap ada push ke branch `main`.

---

## ❌ Troubleshooting

### Build Failed
**Problem:** Error saat npm install/build

**Solution:**
```bash
# Cek package.json scripts
# Pastikan "build" dan "web" script ada

# Test lokal dulu
npm install
npm run build
npm run web
```

### Database Connection Error
**Problem:** Cannot connect to MySQL

**Solution:**
1. Cek Railway Variables → pastikan MySQL variables ter-set
2. Update connection string di code
3. Pastikan MySQL service running

### Custom Domain Tidak Jalan
**Problem:** Domain tidak resolve

**Solution:**
1. Cek DNS propagation: https://dnschecker.org
2. Pastikan CNAME record benar
3. Tunggu propagasi (bisa 24 jam)
4. Gunakan Railway domain dulu untuk testing

### 500 Hours Habis
**Problem:** Free tier limit exceeded

**Solution:**
1. Upgrade ke Hobby plan ($5/month)
2. Atau deploy di service lain (Render, Fly.io)
3. Atau optimasi agar tidak always-on

---

## 💰 Pricing

**Starter (Free):**
- 500 hours/month
- $5 included credit
- Good for development

**Hobby ($5/month):**
- Unlimited hours
- $5 included credit
- Production ready

**Pro ($20/month):**
- Team features
- Priority support
- $20 included credit

Link: https://railway.app/pricing

---

## ✅ Checklist Deploy

- [ ] Repository pushed ke GitHub
- [ ] Project created di Railway
- [ ] MySQL database added
- [ ] Database tables imported
- [ ] Environment variables set
- [ ] Build success
- [ ] Service running
- [ ] Railway domain working
- [ ] Custom domain added (optional)
- [ ] DNS CNAME updated (optional)
- [ ] SSL certificate active
- [ ] Dashboard accessible
- [ ] Socket.IO connection working

---

## 🆘 Butuh Bantuan?

1. **Railway Docs:** https://docs.railway.app
2. **Railway Discord:** https://discord.gg/railway
3. **Dashboard Lokal:** http://localhost:3000/deployment-guide.html

---

**Selamat! Aplikasi WhatsApp API Anda sudah online di Railway! 🎉**

Akses di: `https://waapi.demoj35.site`
