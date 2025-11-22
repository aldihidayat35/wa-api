# 📡 WhatsApp Activity Logs API

REST API untuk mengelola activity logs WhatsApp Multi-Session.

## 📁 Struktur Folder

```
api/
├── .htaccess                      # Apache configuration & CORS
├── config/
│   └── database.php              # Database connection & helpers
├── logs/
│   ├── create.php                # POST - Create new log
│   ├── get.php                   # GET  - Retrieve logs
│   ├── stats.php                 # GET  - Get statistics
│   ├── export.php                # GET  - Export logs (CSV/JSON)
│   └── clear.php                 # POST - Delete logs
├── test_connection.php           # Test database connection
└── generate_sample_logs.php      # Generate sample data
```

## 🚀 Endpoints

### Base URL
```
http://localhost/Baileys/api/
```

### 1. Create Log
**Endpoint:** `POST /logs/create.php`

**Request:**
```json
{
  "log_type": "message",
  "title": "Pesan Terkirim",
  "description": "Berhasil mengirim pesan ke 628123456789",
  "session_id": "session1",
  "phone_number": "628123456789",
  "metadata": {
    "messageId": "msg123",
    "type": "text"
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

### 2. Get Logs
**Endpoint:** `GET /logs/get.php`

**Query Parameters:**
- `log_type` - all, message, session, error, system, api
- `session_id` - Filter by session
- `search` - Search in title & description
- `limit` - Max results (default: 100)
- `offset` - Pagination offset (default: 0)
- `date_from` - Start date (YYYY-MM-DD)
- `date_to` - End date (YYYY-MM-DD)

**Example:**
```
GET /logs/get.php?log_type=message&limit=50&offset=0
```

**Response:**
```json
{
  "success": true,
  "message": "Logs retrieved successfully",
  "data": {
    "logs": [
      {
        "id": 1,
        "log_type": "message",
        "title": "Pesan Terkirim",
        "description": "Berhasil mengirim pesan",
        "session_id": "session1",
        "phone_number": "628123456789",
        "metadata": {...},
        "created_at": "2025-01-23 10:30:00"
      }
    ],
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

### 3. Get Statistics
**Endpoint:** `GET /logs/stats.php`

**Query Parameters:**
- `period` - today, week, month, all (default: today)

**Example:**
```
GET /logs/stats.php?period=week
```

**Response:**
```json
{
  "success": true,
  "data": {
    "period": "week",
    "overall": {
      "total_activities": 125,
      "total_messages": 85,
      "total_errors": 5,
      "active_sessions": 3
    },
    "activity_by_type": [
      {"log_type": "message", "count": 85},
      {"log_type": "session", "count": 20},
      {"log_type": "error", "count": 5}
    ],
    "daily_activity": [
      {"date": "2025-01-23", "count": 45},
      {"date": "2025-01-22", "count": 38}
    ],
    "top_sessions": [
      {"session_id": "session1", "count": 50},
      {"session_id": "session2", "count": 35}
    ]
  }
}
```

---

### 4. Export Logs
**Endpoint:** `GET /logs/export.php`

**Query Parameters:**
- `format` - csv or json (default: csv)
- `log_type` - Filter by type
- `date_from` - Start date
- `date_to` - End date

**Example:**
```
GET /logs/export.php?format=csv&log_type=message
```

**Response:** Downloads CSV or JSON file

---

### 5. Clear Logs
**Endpoint:** `POST /logs/clear.php`

**Request (Delete all):**
```json
{
  "action": "all"
}
```

**Request (Delete old logs):**
```json
{
  "action": "old",
  "days": 90
}
```

**Request (Delete by type):**
```json
{
  "action": "by_type",
  "log_type": "message"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Deleted 150 logs successfully",
  "data": {
    "deleted_count": 150
  }
}
```

---

## 🔧 Utility Endpoints

### Test Connection
**Endpoint:** `GET /test_connection.php`

Test database connection dengan UI visual.

### Generate Sample Logs
**Endpoint:** `GET /generate_sample_logs.php`

Generate 15 sample logs untuk testing (message, session, error, system, api).

---

## 🛡️ Security

### CORS
Semua endpoint support CORS dengan headers:
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

### Input Validation
Semua input divalidasi menggunakan `Validator` class:
- Required fields
- Data type validation
- Max length checks
- Enum validation

### SQL Injection Prevention
- Prepared statements dengan PDO
- Parameter binding
- Input sanitization

---

## 📦 Helper Classes

### Database
```php
$database = new Database();
$conn = $database->getConnection();
```

### Response
```php
Response::success('Message', $data, 200);
Response::error('Error message', 400);
```

### Request
```php
$body = Request::getBody();
$query = Request::getQuery('param');
$ip = Request::getClientIP();
```

### Validator
```php
$rules = [
  'email' => ['required' => true, 'email' => true],
  'name' => ['required' => true, 'max' => 100]
];
$errors = Validator::validate($data, $rules);
```

---

## 🧪 Testing

### Via Browser
```
http://localhost/Baileys/api/test_connection.php
http://localhost/Baileys/api/generate_sample_logs.php
http://localhost/Baileys/api/logs/get.php?log_type=all
http://localhost/Baileys/api/logs/stats.php?period=today
```

### Via JavaScript
```javascript
// Create log
fetch('http://localhost/Baileys/api/logs/create.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    log_type: 'message',
    title: 'Test',
    description: 'Testing API'
  })
})
.then(res => res.json())
.then(data => console.log(data));

// Get logs
fetch('http://localhost/Baileys/api/logs/get.php?log_type=all')
  .then(res => res.json())
  .then(data => console.log(data));
```

### Via cURL
```bash
# Create log
curl -X POST http://localhost/Baileys/api/logs/create.php \
  -H "Content-Type: application/json" \
  -d '{"log_type":"message","title":"Test","description":"Testing"}'

# Get logs
curl http://localhost/Baileys/api/logs/get.php?log_type=all

# Get stats
curl http://localhost/Baileys/api/logs/stats.php?period=today

# Clear logs
curl -X POST http://localhost/Baileys/api/logs/clear.php \
  -H "Content-Type: application/json" \
  -d '{"action":"old","days":90}'
```

---

## 📊 Database Schema

Tables:
- `activity_logs` - Main log table
- `log_statistics` - Daily aggregated stats
- `sessions` - Session tracking
- `message_logs` - Detailed message records

Views:
- `v_daily_activity` - Daily activity summary
- `v_session_performance` - Session performance metrics
- `v_recent_errors` - Recent error logs

Stored Procedures:
- `GetActivityLogs()` - Complex log retrieval
- `UpdateDailyStats()` - Update statistics

Triggers:
- `after_message_insert` - Auto-update session stats

Events:
- `cleanup_old_logs` - Auto-delete logs > 90 days

---

## 🚀 Deployment

### Production Checklist
- [ ] Change database credentials
- [ ] Create dedicated database user
- [ ] Enable SSL/HTTPS
- [ ] Implement API authentication
- [ ] Set up rate limiting
- [ ] Configure log rotation
- [ ] Enable database backup
- [ ] Monitor error logs

### Environment Variables
Create `.env` file:
```env
DB_HOST=localhost
DB_NAME=whatsapp_logs
DB_USER=your_user
DB_PASS=your_password
```

---

## 📚 Documentation

- **Quick Start:** `QUICKSTART_DATABASE.md`
- **Full Setup:** `DATABASE_SETUP.md`
- **SQL Schema:** `sql/create_database.sql`

---

## 💡 Tips

1. **Pagination:** Gunakan `limit` & `offset` untuk data besar
2. **Caching:** Implementasi Redis untuk stats endpoint
3. **Monitoring:** Setup logging untuk API requests
4. **Backup:** Daily database backup recommended
5. **Performance:** Index sudah optimal, jangan hapus!

---

Created with ❤️ for WhatsApp Multi-Session Bot
