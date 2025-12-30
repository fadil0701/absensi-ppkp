# Panduan Integrasi Sistem Absensi PPKP

Dokumen ini menjelaskan berbagai cara untuk mengintegrasikan sistem absensi dengan sistem lain.

## 1. REST API (Sudah Tersedia)

Sistem ini sudah memiliki REST API dengan Laravel Sanctum untuk autentikasi.

### Base URL
```
http://your-domain.com/api
```

### Autentikasi
1. Login untuk mendapatkan token:
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

2. Gunakan token di header setiap request:
```http
Authorization: Bearer {token}
```

### Endpoint yang Tersedia

#### Presensi
- `POST /api/presensi/check-in` - Check in
- `POST /api/presensi/check-out` - Check out
- `GET /api/presensi/riwayat` - Riwayat presensi
- `GET /api/presensi/{id}` - Detail presensi

#### Laporan
- `GET /api/laporan/akumulasi` - Laporan akumulasi bulanan
- `GET /api/laporan/telat` - Laporan keterlambatan
- `GET /api/laporan/tidak-masuk` - Laporan tidak masuk

#### Jadwal
- `GET /api/jadwal/pegawai/{pegawai_id}` - Jadwal pegawai
- `POST /api/jadwal/pegawai` - Buat jadwal

## 2. Webhook untuk Real-time Notifications

Webhook memungkinkan sistem lain menerima notifikasi real-time ketika ada event terjadi.

### Implementasi Webhook

1. Buat tabel untuk menyimpan webhook subscriptions
2. Buat endpoint untuk register webhook
3. Kirim HTTP POST ke webhook URL ketika event terjadi

### Contoh Event:
- Presensi baru dibuat
- Presensi disetujui/ditolak
- Izin/cuti baru dibuat
- Laporan selesai di-generate

## 3. Database View/Sharing

Membuat database view yang bisa diakses oleh sistem lain untuk read-only access.

### Keuntungan:
- Real-time data
- Tidak perlu API call
- Performa lebih cepat untuk query kompleks

### Contoh View:
```sql
CREATE VIEW v_presensi_harian AS
SELECT 
    p.id,
    p.pegawai_id,
    pg.nip,
    pg.nama,
    p.tanggal,
    p.jenis,
    p.waktu_absen,
    p.status,
    s.nama_satpelkes
FROM presensi p
JOIN pegawai pg ON p.pegawai_id = pg.id
LEFT JOIN satpelkes s ON p.satpelkes_id = s.id
WHERE p.status IN ('IN_ZONE', 'APPROVED');
```

## 4. File-based Integration (CSV/JSON Export)

Sistem dapat mengexport data ke file yang bisa di-import oleh sistem lain.

### Format Export:
- CSV untuk Excel/Spreadsheet
- JSON untuk sistem modern
- XML untuk sistem legacy

### Schedule Export:
- Harian (cron job)
- Bulanan
- On-demand via API

## 5. Message Queue (RabbitMQ/Redis Queue)

Untuk integrasi asinkron dengan sistem lain menggunakan queue.

### Use Case:
- Sync data ke sistem HR
- Kirim notifikasi ke sistem payroll
- Update sistem inventory

## 6. API Key Authentication (Sistem-to-Sistem) ✅ SUDAH TERSEDIA

Untuk integrasi sistem-to-system tanpa user login.

### Cara Menggunakan API Key:

1. **Buat API Key** (Admin only):
   - Login sebagai Admin
   - Buka menu "API Key Management"
   - Klik "Tambah API Key"
   - Isi informasi dan simpan
   - **PENTING**: Simpan API Key, ConsID, UserKey, dan Secret Key yang ditampilkan (hanya muncul sekali!)

2. **Metode Autentikasi**:
   
   Sistem mendukung **2 metode autentikasi**:

   **Metode 1: API Key (Recommended)**
   ```http
   GET /api/integration/akumulasi?bulan=2025-12
   X-API-KEY: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   X-SECRET-KEY: sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```

   **Metode 2: ConsID & UserKey**
   ```http
   GET /api/integration/akumulasi?bulan=2025-12
   X-CONSID: cons_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   X-USERKEY: usr_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   X-SECRET-KEY: sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```

3. **Endpoint yang Support API Key**:
   - `GET /api/integration/akumulasi` - Laporan akumulasi bulanan
   - `GET /api/integration/presensi-harian` - Data presensi harian
   - `GET /api/integration/export` - Export data

### Fitur API Key:
- ✅ API Key & Secret Key generation
- ✅ ConsID (Consumer ID) & UserKey generation
- ✅ IP Whitelist (optional)
- ✅ Scopes/Permissions (optional)
- ✅ Rate Limiting
- ✅ Expiration Date (optional)
- ✅ Last Used Tracking
- ✅ Regenerate Secret Key

### Contoh Request dengan API Key:
```bash
# Metode 1: Menggunakan API Key
curl -X GET "http://your-domain.com/api/integration/akumulasi?bulan=2025-12" \
  -H "X-API-KEY: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-SECRET-KEY: sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"

# Metode 2: Menggunakan ConsID & UserKey
curl -X GET "http://your-domain.com/api/integration/akumulasi?bulan=2025-12" \
  -H "X-CONSID: cons_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-USERKEY: usr_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-SECRET-KEY: sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
```

### Penjelasan ConsID & UserKey:
- **ConsID (Consumer ID)**: Identifier untuk aplikasi/konsumen yang menggunakan API (satu aplikasi = satu ConsID)
- **UserKey**: Identifier untuk user/pengguna spesifik dalam aplikasi tersebut (satu user = satu UserKey)
- Keduanya digunakan bersama dengan Secret Key untuk autentikasi yang lebih granular

# 1. Login untuk mendapatkan token
curl -X POST http://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# 2. Gunakan token untuk akses data
curl -X GET http://your-domain.com/api/integration/akumulasi?bulan=2025-12 \
  -H "Authorization: Bearer {token}"
  -H "X-API-KEY: your-api-key"  