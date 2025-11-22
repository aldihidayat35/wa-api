# 📊 WhatsApp Multi-Session Activity Logging System

Sistem logging lengkap dengan MySQL database integration untuk WhatsApp Multi-Session bot menggunakan Baileys.

---

## ✨ Features

### 🎯 Core Features
- ✅ **Modular Layout** - Header, Sidebar, Footer terpisah
- ✅ **Send Message Page** - Text/Image, Single/Bulk, dengan delay management
- ✅ **Activity Log Page** - Human-readable logs dengan filter & search
- ✅ **MySQL Database** - Persistent storage dengan full API
- ✅ **Hybrid Approach** - LocalStorage untuk speed, Database untuk reliability
- ✅ **Statistics Dashboard** - Real-time statistics dan analytics
- ✅ **Export Functionality** - Export ke CSV atau JSON

### 📡 API Features
- ✅ **5 REST Endpoints** - Create, Get, Stats, Export, Clear
- ✅ **Advanced Filtering** - By type, session, date range, search
- ✅ **Pagination** - Efficient data loading
- ✅ **CORS Support** - Cross-origin requests enabled
- ✅ **Input Validation** - Comprehensive validation layer

### 🗄️ Database Features
- ✅ **4 Tables** - activity_logs, log_statistics, sessions, message_logs
- ✅ **Stored Procedures** - GetActivityLogs, UpdateDailyStats
- ✅ **Triggers** - Auto-update session statistics
- ✅ **Views** - Daily activity, session performance, recent errors
- ✅ **Event Scheduler** - Auto-cleanup old logs (90 days)
- ✅ **Full-text Search** - Fast search on descriptions
- ✅ **Optimized Indexes** - Fast queries

---

## 📁 Project Structure

```
Baileys/
├── public/                          # Frontend files
│   ├── index.html                  # Dashboard/home page
│   ├── send-message.html           # Send message page
│   ├── send-message.js             # Message sending logic
│   ├── log-activity.html           # Activity log page
│   ├── log-activity.js             # Log management logic
│   └── components/                 # Reusable components
│       ├── header.html             # Header component
│       ├── sidebar.html            # Sidebar navigation
│       └── footer.html             # Footer component
│
├── api/                            # Backend API
│   ├── .htaccess                   # Apache config & CORS
│   ├── config/
│   │   └── database.php            # DB connection & helpers
│   ├── logs/
│   │   ├── create.php              # Create log endpoint
│   │   ├── get.php                 # Get logs endpoint
│   │   ├── stats.php               # Statistics endpoint
│   │   ├── export.php              # Export endpoint
│   │   └── clear.php               # Clear logs endpoint
│   ├── test_connection.php         # Test database connection
│   ├── generate_sample_logs.php    # Generate test data
│   └── README.md                   # API documentation
│
├── sql/
│   └── create_database.sql         # Database schema (4 tables, procedures, triggers, views)
│
├── docs/                           # Documentation (created)
│   ├── DATABASE_SETUP.md           # Complete database setup guide
│   ├── QUICKSTART_DATABASE.md      # Quick start guide
│   ├── INSTALLATION_CHECKLIST.md   # Installation checklist
│   └── PROJECT_OVERVIEW.md         # This file
│
└── [other Baileys files...]        # Original Baileys library files
```

---

## 🚀 Quick Start

### 1️⃣ Import Database (1 menit)
```bash
mysql -u root -p < sql/create_database.sql
```

### 2️⃣ Test Connection (30 detik)
```
http://localhost/Baileys/api/test_connection.php
```

### 3️⃣ Generate Sample Data
```
http://localhost/Baileys/api/generate_sample_logs.php
```

### 4️⃣ Open Application
```
http://localhost/Baileys/public/log-activity.html
```

**Baca lengkap:** `QUICKSTART_DATABASE.md`

---

## 📚 Documentation

| File | Description |
|------|-------------|
| `DATABASE_SETUP.md` | Complete database setup, configuration, API docs, troubleshooting |
| `QUICKSTART_DATABASE.md` | 3-step quick start guide |
| `INSTALLATION_CHECKLIST.md` | Step-by-step installation checklist |
| `api/README.md` | API endpoints documentation |

---

## 🗄️ Database Schema

### Tables

#### 1. **activity_logs** (Main Log Table)
Stores all system activities with full metadata support.

```sql
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_type ENUM('message', 'session', 'error', 'system', 'api'),
    title VARCHAR(255),
    description TEXT,
    session_id VARCHAR(100),
    phone_number VARCHAR(20),
    metadata JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_log_type_created (log_type, created_at),
    INDEX idx_session_created (session_id, created_at),
    FULLTEXT idx_description (description)
);
```

#### 2. **log_statistics** (Daily Aggregates)
```sql
CREATE TABLE log_statistics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stat_date DATE UNIQUE,
    total_messages INT DEFAULT 0,
    total_sessions INT DEFAULT 0,
    total_errors INT DEFAULT 0,
    total_api_calls INT DEFAULT 0,
    total_activities INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 3. **sessions** (Session Tracking)
```sql
CREATE TABLE sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(100) UNIQUE,
    phone_number VARCHAR(20),
    status ENUM('connected', 'disconnected', 'qr_pending'),
    last_active TIMESTAMP,
    total_messages_sent INT DEFAULT 0,
    total_errors INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 4. **message_logs** (Detailed Message Records)
```sql
CREATE TABLE message_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_log_id INT,
    message_id VARCHAR(255),
    recipient VARCHAR(20),
    message_type ENUM('text', 'image', 'video', 'document', 'audio'),
    status ENUM('sent', 'delivered', 'read', 'failed'),
    error_message TEXT,
    sent_at TIMESTAMP,
    delivered_at TIMESTAMP,
    read_at TIMESTAMP,
    FOREIGN KEY (activity_log_id) REFERENCES activity_logs(id)
);
```

---

## 📡 API Endpoints

### Base URL
```
http://localhost/Baileys/api/logs/
```

### 1. Create Log
```http
POST /create.php
Content-Type: application/json

{
  "log_type": "message",
  "title": "Pesan Terkirim",
  "description": "Berhasil mengirim pesan",
  "session_id": "session1",
  "phone_number": "628123456789",
  "metadata": {"messageId": "msg123"}
}
```

### 2. Get Logs
```http
GET /get.php?log_type=message&limit=50&offset=0
```

### 3. Statistics
```http
GET /stats.php?period=today
```

### 4. Export
```http
GET /export.php?format=csv&log_type=all
```

### 5. Clear Logs
```http
POST /clear.php
Content-Type: application/json

{
  "action": "old",
  "days": 90
}
```

**Full API docs:** `api/README.md`

---

## 🎨 Frontend Pages

### 1. Send Message (`send-message.html`)

**Features:**
- 4 message types: Text-Single, Text-Bulk, Image-Single, Image-Bulk
- Manual or database-based recipient input
- Bulk recipient management with chip UI
- Image upload with preview
- Random delay between messages (1-5 seconds configurable)
- Progress tracking for bulk sending
- Real-time validation

**Usage:**
1. Select message type (radio buttons)
2. Choose input method (manual/bulk)
3. Enter recipients
4. Compose message or upload image
5. Set delay (for bulk)
6. Click send

### 2. Activity Log (`log-activity.html`)

**Features:**
- 4 statistics cards: Messages, Sessions, Errors, Total Activities
- 6 filter buttons: All, Message, Session, Error, System, API
- Real-time search across title & description
- Export to CSV or JSON
- Clear all logs functionality
- Live indicator showing real-time status
- Human-readable timestamps
- Metadata display with badges
- Pagination support

**Usage:**
1. View logs in timeline format
2. Filter by type
3. Search by keywords
4. Export logs
5. Clear old data

---

## 🔧 Configuration

### Database Credentials

Edit `api/config/database.php`:

```php
private $host = "localhost";
private $db_name = "whatsapp_logs";
private $username = "root";        // Change if different
private $password = "";            // Add password if needed
```

### Apache Configuration

`.htaccess` already configured for:
- CORS headers
- Security headers
- PHP settings
- Rewrite rules

---

## 🧪 Testing

### Test Database Connection
```
http://localhost/Baileys/api/test_connection.php
```

### Generate Sample Data
```
http://localhost/Baileys/api/generate_sample_logs.php
```

### Test API via cURL
```bash
# Create log
curl -X POST http://localhost/Baileys/api/logs/create.php \
  -H "Content-Type: application/json" \
  -d '{"log_type":"message","title":"Test","description":"Testing API"}'

# Get logs
curl http://localhost/Baileys/api/logs/get.php?log_type=all

# Get statistics
curl http://localhost/Baileys/api/logs/stats.php?period=today
```

---

## 🔐 Security

### Implemented
- ✅ SQL Injection prevention (prepared statements)
- ✅ CORS headers configured
- ✅ Input validation
- ✅ XSS protection headers
- ✅ Parameter sanitization

### Recommended for Production
- [ ] API authentication (JWT tokens)
- [ ] Rate limiting
- [ ] HTTPS/SSL
- [ ] Database user with limited privileges
- [ ] Environment variables for credentials
- [ ] IP whitelisting

---

## 🛠️ Maintenance

### Auto-Cleanup

Database schema includes automatic cleanup event:

```sql
-- Runs daily at 2 AM
CREATE EVENT cleanup_old_logs
ON SCHEDULE EVERY 1 DAY
STARTS '2025-01-01 02:00:00'
DO
  DELETE FROM activity_logs 
  WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

Enable event scheduler:
```sql
SET GLOBAL event_scheduler = ON;
```

### Manual Backup

```bash
# Daily backup
mysqldump -u root -p whatsapp_logs > backup_$(date +%Y%m%d).sql

# Restore
mysql -u root -p whatsapp_logs < backup_20250123.sql
```

---

## 📊 Statistics & Analytics

### Available Metrics

1. **Overall Statistics**
   - Total activities
   - Total messages sent
   - Total errors
   - Active sessions count

2. **Activity by Type**
   - Message logs
   - Session events
   - Error logs
   - System events
   - API calls

3. **Daily Activity**
   - 7-day chart data
   - Trend analysis
   - Peak hours

4. **Top Sessions**
   - Most active sessions
   - Success rate
   - Error rate

### Views for Reporting

```sql
-- Daily activity summary
SELECT * FROM v_daily_activity;

-- Session performance metrics
SELECT * FROM v_session_performance;

-- Recent errors (last 24 hours)
SELECT * FROM v_recent_errors;
```

---

## 🚧 Known Limitations

1. **LocalStorage**: 5-10MB limit per domain
2. **Event Scheduler**: Requires MySQL event scheduler enabled
3. **Full-text Search**: Minimum word length 4 characters (configurable)
4. **CORS**: Currently allows all origins (`*`)

---

## 🎯 Future Enhancements

### Planned Features
- [ ] Real-time dashboard with charts
- [ ] WebSocket integration for live updates
- [ ] User authentication & authorization
- [ ] Multi-language support (i18n)
- [ ] Email notifications for errors
- [ ] Advanced analytics with charts
- [ ] Message scheduling
- [ ] Template management
- [ ] Contact management
- [ ] Bulk import from CSV/Excel

### Performance Improvements
- [ ] Redis caching for statistics
- [ ] Database query optimization
- [ ] Lazy loading for large datasets
- [ ] Infinite scroll pagination
- [ ] Service worker for offline support

---

## 🐛 Troubleshooting

### Common Issues

**Database connection failed**
- Check MySQL service running
- Verify credentials in `database.php`
- Test: `mysql -u root -p`

**CORS errors**
- Check `.htaccess` exists in `api/`
- Verify Apache `mod_headers` enabled
- Clear browser cache

**API returns 500**
- Check PHP error log
- Enable error display (dev only)
- Verify database schema imported

**Logs not saving**
- Check browser console for errors
- Test API endpoint directly
- Verify database permissions

**Full troubleshooting guide:** `DATABASE_SETUP.md`

---

## 📞 Support & Resources

### Documentation
- `DATABASE_SETUP.md` - Complete setup guide
- `QUICKSTART_DATABASE.md` - Quick start
- `INSTALLATION_CHECKLIST.md` - Step-by-step checklist
- `api/README.md` - API documentation

### Debugging
- Browser console (F12)
- PHP error log: `C:\laragon\logs\error.log`
- MySQL error log: `C:\laragon\data\mysql\error.log`
- Apache error log: `C:\laragon\logs\apache_error.log`

---

## 📝 Changelog

### Version 1.0.0 (2025-01-23)

**Added:**
- Modular component structure (header, sidebar, footer)
- Send Message page with 4 message types
- Activity Log page with filtering & search
- Complete MySQL database schema
- 5 REST API endpoints
- Helper classes (Database, Response, Request, Validator)
- Test connection utility
- Sample data generator
- Comprehensive documentation

**Features:**
- Hybrid LocalStorage + Database approach
- Real-time statistics
- Export to CSV/JSON
- Auto-cleanup old logs
- Full-text search
- Stored procedures & triggers
- Optimized indexes

---

## 📄 License

This project extends [Baileys](https://github.com/WhiskeySockets/Baileys) WhatsApp library.

Original Baileys license applies to core library.
Custom activity logging system & database integration: MIT License

---

## 🙏 Credits

- **Baileys Library** - WhatsApp Web API
- **Metronic 8** - UI theme & components
- **Bootstrap 5** - Responsive framework
- **Socket.IO** - Real-time communication

---

## 📧 Contact

For questions or issues:
1. Check documentation files
2. Review error logs
3. Test API endpoints via Postman
4. Verify database schema

---

**Built with ❤️ for WhatsApp Multi-Session Bot**

Last updated: 2025-01-23
Version: 1.0.0
