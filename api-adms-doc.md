# ADMS Fingerprint Solution - API Documentation

Dokumentasi lengkap API untuk integrasi data absensi dengan aplikasi eksternal.

---

## 🔐 Autentikasi

Semua API endpoint memerlukan token autentikasi. Token dapat dibuat melalui halaman **Admin > API Tokens** di dashboard.

### Header Request
```
Authorization: Bearer {API_TOKEN}
```

### Contoh
```bash
curl -X GET "https://your-domain.com/api/v1/hr/attendances" \
  -H "Authorization: Bearer your-api-token-here"
```

---

## 📋 Endpoints

### 1. Get All Attendances

Mengambil semua data absensi dengan filter opsional.

**Endpoint:** `GET /api/v1/hr/attendances`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `start_date` | string | No | Tanggal mulai (format: YYYY-MM-DD) |
| `end_date` | string | No | Tanggal akhir (format: YYYY-MM-DD) |
| `employee_id` | string | No | Filter berdasarkan ID karyawan |
| `limit` | integer | No | Jumlah record per halaman (default: 50, max: 100) |
| `offset` | integer | No | Jumlah record yang di-skip (default: 0) |

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_id": "251",
      "timestamp": "2026-01-19T09:08:22+07:00",
      "device_sn": "SPK7245000764",
      "check_type": 0,
      "check_type_label": "Check In",
      "verify_mode": 1,
      "verify_mode_label": "Fingerprint",
      "work_code": null,
      "status": {
        "status1": 0,
        "status2": 1,
        "status3": null,
        "status4": null,
        "status5": null
      },
      "created_at": "2026-01-19T09:08:31+07:00"
    }
  ],
  "meta": {
    "total": 627,
    "count": 50,
    "per_page": 50,
    "current_page": 1,
    "total_pages": 13
  }
}
```

---

### 2. Get Attendance by ID

Mengambil detail satu record absensi berdasarkan ID.

**Endpoint:** `GET /api/v1/hr/attendances/{id}`

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | ID record absensi |

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "employee_id": "251",
    "timestamp": "2026-01-19T09:08:22+07:00",
    "device_sn": "SPK7245000764",
    "check_type": 0,
    "check_type_label": "Check In",
    "verify_mode": 1,
    "verify_mode_label": "Fingerprint",
    "work_code": null,
    "status": {
      "status1": 0,
      "status2": 1,
      "status3": null,
      "status4": null,
      "status5": null
    },
    "created_at": "2026-01-19T09:08:31+07:00"
  }
}
```

---

### 3. Get Attendances by Employee

Mengambil data absensi untuk karyawan tertentu.

**Endpoint:** `GET /api/v1/hr/employees/{employee_id}/attendances`

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `employee_id` | string | Yes | ID karyawan |

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `start_date` | string | No | Tanggal mulai (format: YYYY-MM-DD) |
| `end_date` | string | No | Tanggal akhir (format: YYYY-MM-DD) |
| `limit` | integer | No | Jumlah record per halaman (default: 50, max: 100) |
| `offset` | integer | No | Jumlah record yang di-skip (default: 0) |

---

## 📊 Reference Data

### Check Type (status1)

| Value | Label | Description |
|-------|-------|-------------|
| `0` | Check In | Absen masuk |
| `1` | Check Out | Absen keluar |
| `2` | Break Out | Keluar istirahat |
| `3` | Break In | Masuk dari istirahat |
| `4` | OT In | Masuk lembur |
| `5` | OT Out | Keluar lembur |

### Verify Mode (status2)

| Value | Label | Description |
|-------|-------|-------------|
| `1` | Fingerprint | Verifikasi sidik jari |
| `2` | Password | Verifikasi password |
| `3` | Card | Verifikasi kartu RFID |
| `15` | Face Recognition | Verifikasi wajah |

---

## ❌ Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Invalid or missing API token"
  }
}
```

### 404 Not Found
```json
{
  "success": false,
  "error": {
    "code": "RESOURCE_NOT_FOUND",
    "message": "Attendance record not found"
  }
}
```

### 429 Too Many Requests
```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many requests. Please try again later."
  }
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "An error occurred while processing your request"
  }
}
```

---

## 🔄 Webhook (Opsional)

Sistem juga mendukung webhook untuk mengirim data absensi secara real-time ke aplikasi eksternal saat ada absensi baru.

Konfigurasi webhook dapat dilakukan melalui halaman **Admin > Webhooks** di dashboard.

---

## 📝 Contoh Penggunaan

### PHP (cURL)
```php
<?php
$token = 'your-api-token';
$url = 'https://your-domain.com/api/v1/hr/attendances';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url . '?start_date=2026-01-01&end_date=2026-01-31');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$data = json_decode($response, true);

foreach ($data['data'] as $attendance) {
    echo $attendance['employee_id'] . ' - ' . $attendance['check_type_label'] . "\n";
}
```

### JavaScript (Fetch)
```javascript
const token = 'your-api-token';
const url = 'https://your-domain.com/api/v1/hr/attendances';

fetch(`${url}?start_date=2026-01-01&end_date=2026-01-31`, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
.then(res => res.json())
.then(data => {
  data.data.forEach(attendance => {
    console.log(`${attendance.employee_id} - ${attendance.check_type_label}`);
  });
});
```

### Python (Requests)
```python
import requests

token = 'your-api-token'
url = 'https://your-domain.com/api/v1/hr/attendances'

headers = {
    'Authorization': f'Bearer {token}',
    'Accept': 'application/json'
}

params = {
    'start_date': '2026-01-01',
    'end_date': '2026-01-31'
}

response = requests.get(url, headers=headers, params=params)
data = response.json()

for attendance in data['data']:
    print(f"{attendance['employee_id']} - {attendance['check_type_label']}")
```

---

## 📞 Support

Jika ada pertanyaan atau kendala, silakan hubungi administrator sistem.
