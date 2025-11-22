# WhatsApp API (Baileys) - Panduan Deployment

## ⚠️ PENTING: Shared Hosting TIDAK Support

**Aplikasi ini TIDAK BISA dijalankan di shared hosting biasa** seperti:
- RumahWeb shared hosting
- Hostinger shared hosting  
- Niagahoster shared hosting
- cPanel shared hosting lainnya

**Kenapa?**
Aplikasi ini membutuhkan:
- Node.js runtime yang berjalan terus-menerus (persistent process)
- WebSocket (Socket.IO) untuk komunikasi real-time
- Akses terminal dan PM2/process manager
- Port custom (3000) yang selalu aktif

Shared hosting **TIDAK mendukung** semua requirement di atas.

---

## ✅ Solusi: Gunakan VPS atau Cloud Platform

### Pilihan 1: VPS Indonesia (RECOMMENDED)

**Provider & Harga:**
- **Niagahoster VPS** - Rp 60.000/bulan - [Link](https://www.niagahoster.co.id/vps-murah)
- **IDCloudHost VPS** - Rp 70.000/bulan - [Link](https://idcloudhost.com/vps/)
- **Dewaweb VPS** - Rp 100.000/bulan - [Link](https://www.dewaweb.com/cloud-vps)
- **DigitalOcean** - USD 4/bulan (~Rp 60rb) - [Link](https://www.digitalocean.com/)

**Spesifikasi Minimum:**
- CPU: 1 vCore
- RAM: 1 GB (2 GB lebih baik)
- Storage: 20 GB SSD
- OS: Ubuntu 22.04 LTS
- Bandwidth: Unlimited/1TB

**Cara Deploy:**
Lihat panduan lengkap di dashboard → Menu "Panduan Deployment"

---

### Pilihan 2: Cloud Platform (MUDAH & GRATIS)

**Railway.app (Recommended)**
- Free tier: 500 hours/bulan (cukup untuk 1 app)
- Setup mudah via GitHub
- Auto SSL
- [Link](https://railway.app)

**Render.com**
- Free tier: Unlimited
- Auto deploy
- Auto SSL
- [Link](https://render.com)

**Fly.io**  
- Free tier: 3 VM gratis
- Global deployment
- [Link](https://fly.io)

**Cara Deploy:**
1. Push code ke GitHub
2. Connect Railway/Render dengan GitHub
3. Deploy otomatis
4. Add MySQL database
5. Selesai!

---

## 🔧 Setup Domain ke VPS

Jika Anda sudah punya domain `waapi.demoj35.site` di RumahWeb:

### Langkah 1: Update DNS Record
1. Login ke panel RumahWeb
2. Masuk ke Domain Management
3. Pilih domain `waapi.demoj35.site`
4. Klik "Manage DNS"
5. Edit/Tambah A Record:
   ```
   Type: A
   Name: @ (atau waapi)
   Value: [IP VPS Anda, contoh: 103.xxx.xxx.xxx]
   TTL: 3600
   ```
6. Save
7. Tunggu propagasi DNS (5-60 menit)

### Langkah 2: Install Aplikasi di VPS
Lihat panduan lengkap di: **Dashboard → Panduan Deployment**

---

## 📋 Quick Start (VPS)

```bash
# 1. Connect ke VPS via SSH
ssh root@[IP-VPS-ANDA]

# 2. Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# 3. Clone repository
cd /var/www
git clone https://github.com/aldihidayat35/wa-api.git
cd wa-api

# 4. Install dependencies
npm install

# 5. Build aplikasi
npm run build

# 6. Install PM2
sudo npm install -g pm2

# 7. Start aplikasi
pm2 start npm --name "wa-api" -- run web
pm2 save
pm2 startup

# 8. Install & setup Nginx reverse proxy
sudo apt install -y nginx
# [Lihat konfigurasi lengkap di dashboard]

# 9. Install SSL (gratis dari Let's Encrypt)
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d waapi.demoj35.site
```

---

## 🆘 Butuh Bantuan?

1. Baca panduan lengkap: Dashboard → Menu "Panduan Deployment"
2. Lihat dokumentasi API: Dashboard → Menu "Dokumentasi API"
3. Troubleshooting tersedia di halaman Panduan Deployment

---

## 💰 Estimasi Biaya

**Shared Hosting (TIDAK BISA):**
- ❌ Rp 20.000 - 50.000/bulan
- Tidak support Node.js persistent
- Tidak support Socket.IO

**VPS (BISA - RECOMMENDED):**
- ✅ Rp 60.000 - 150.000/bulan
- Full control
- Support semua teknologi
- Dedicated resources

**Cloud Platform (BISA - TERMUDAH):**
- ✅ Gratis (free tier) - Rp 100.000/bulan
- Setup 5 menit
- Auto scaling
- Auto SSL

---

**Kesimpulan:** 

Untuk domain `waapi.demoj35.site`, Anda perlu:
1. **Beli VPS** (Rp 60rb/bulan) ATAU gunakan **Railway.app** (gratis)
2. **Ubah DNS** domain ke IP VPS atau CNAME Railway
3. **Deploy aplikasi** mengikuti panduan di dashboard
4. **Setup SSL** untuk HTTPS

Domain tetap di RumahWeb, tapi **server/hosting pindah ke VPS/Cloud**.
