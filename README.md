# 🌬️ Sistem Pemantauan Kualitas Udara

## 📋 Deskripsi Proyek

Sistem Pemantauan Kualitas Udara adalah platform web terintegrasi yang dirancang untuk memantau kondisi kualitas udara secara real-time di 5 lokasi strategis di Yogyakarta. Sistem ini dikembangkan untuk **Dinas Lingkungan Hidup** sebagai solusi monitoring polusi udara yang akurat dan mudah diakses.

### Kelompok: 3
### Klien: Dinas Lingkungan Hidup
### Lokasi: Yogyakarta

**5 Titik Monitoring:**
1. 📍 **Sleman** - Pusat kota, area perkantoran
2. 🏭 **Godean** - Area industri  
3. ⛰️ **Kaliurang** - Pegunungan/wisata (udara terbersih)
4. 🌊 **Bantul** - Area pesisir selatan
5. 🎓 **Condongcatur** - Area kampus & permukiman

---

## ✨ Fitur Utama

- ✅ **Dashboard Real-time** - Monitoring data kualitas udara secara langsung

- 🔔 **Sistem Peringatan Dini** - Alert otomatis saat kualitas udara berbahaya
- 📍 **Multi-Lokasi** - Monitoring dari berbagai titik sensor
- 📱 **Responsive Design** - Dapat diakses dari desktop, tablet, dan smartphone
- 📈 **Data Historis** - Menyimpan dan menganalisis tren jangka panjang
- 🔌 **REST API** - Endpoint untuk integrasi dengan sensor IoT

---

## 🛠️ Teknologi yang Digunakan

- **Frontend:**
  - HTML5
  - CSS3 (Bootstrap 5)
  - JavaScript (Vanilla JS)


- **Backend:**
  - PHP 7.4+ (Pure PHP, tanpa framework)
  - MySQL Database

- **Libraries:**
  - Bootstrap 5.3.0
  - Font Awesome 6.4.0


---

## 📁 Struktur Folder

```
sistem-kualitas-udara/
│
├── index.php              # Dashboard real-time
├── data.php               # Halaman tabel data

├── about.php              # Informasi proyek
├── alert.php              # Halaman peringatan dini
├── koneksi.php            # Koneksi database
│
├── api/
│   └── post_data.php      # API endpoint untuk sensor
│
├── assets/
│   ├── css/
│   │   └── style.css      # Custom CSS
│   └── js/
│       └── script.js      # Custom JavaScript
│
└── database/
    └── setup.sql          # Script SQL database
```

---

## 🚀 Cara Instalasi di XAMPP

### Langkah 1: Persiapan
1. Download dan install XAMPP dari https://www.apachefriends.org/
2. Download semua file proyek ini

### Langkah 2: Setup Project
1. Copy folder `sistem-kualitas-udara` ke dalam folder `htdocs` di XAMPP
   - Lokasi default: `C:\xampp\htdocs\` (Windows) atau `/Applications/XAMPP/htdocs/` (Mac)

### Langkah 3: Setup Database
1. Buka XAMPP Control Panel
2. Start **Apache** dan **MySQL**
3. Buka browser dan akses: `http://localhost/phpmyadmin`
4. Buat database baru dengan nama: `kualitas_udara`
5. Import file SQL:
   - Klik database `kualitas_udara`
   - Pilih tab **Import**
   - Pilih file `database/setup.sql`
   - Klik **Go**

### Langkah 4: Konfigurasi (Opsional)
Jika menggunakan password MySQL yang berbeda, edit file `koneksi.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Ganti dengan password MySQL Anda
define('DB_NAME', 'kualitas_udara');
```

### Langkah 5: Akses Website
Buka browser dan akses: `http://localhost/sistem-kualitas-udara/`

---

## 🌐 Cara Deploy ke Hosting

### Persiapan
1. Siapkan hosting PHP dengan MySQL (shared hosting, VPS, dll)
2. Catat informasi:
   - Host database (biasanya `localhost`)
   - Username database
   - Password database
   - Nama database

### Langkah Deploy
1. **Upload File:**
   - Gunakan FTP/FileZilla atau File Manager cPanel
   - Upload semua file ke folder `public_html` atau `www`

2. **Setup Database:**
   - Login ke cPanel → MySQL Databases
   - Buat database baru
   - Buat user database baru
   - Berikan semua privilege ke user
   - Import file `setup.sql` melalui phpMyAdmin

3. **Edit Koneksi:**
   - Edit file `koneksi.php` dengan informasi database hosting:
   
   ```php
   define('DB_HOST', 'localhost');  // atau host yang diberikan
   define('DB_USER', 'username_db');
   define('DB_PASS', 'password_db');
   define('DB_NAME', 'nama_database');
   ```

4. **Set Permissions:**
   - Pastikan folder memiliki permission yang benar (biasanya 755)
   - File PHP sebaiknya 644

5. **Test:**
   - Akses website melalui domain Anda
   - Cek apakah semua fitur berjalan normal

---

## 🔌 Penggunaan API

### Endpoint: POST Data Sensor

**URL:** `http://localhost/sistem-kualitas-udara/api/post_data.php`

**Method:** POST

**Content-Type:** application/json

**Request Body:**
```json
{
    "lokasi": "Sleman",
    "pm25": 45.5,
    "pm10": 78.2,
    "co": 2.3,
    "no2": 0.08,
    "so2": 0.05,
    "o3": 0.06,
    "suhu": 32.5,
    "kelembapan": 68.0
}
```

**Response (Success):**
```json
{
    "status": "success",
    "message": "Data berhasil disimpan",
    "data": {
        "id": 1,
        "lokasi": "Sleman",
        "aqi": 118,
        "status_kualitas": "Tidak Sehat untuk Kelompok Sensitif",
        "alerts_created": 0,
        "alerts": []
    }
}
```

### Test API dengan cURL:

```bash
curl -X POST http://localhost/sistem-kualitas-udara/api/post_data.php \
  -H "Content-Type: application/json" \
  -d '{
    "lokasi": "Kaliurang",
    "pm25": 25.5,
    "pm10": 42.2,
    "co": 1.1,
    "no2": 0.04,
    "so2": 0.03,
    "o3": 0.03,
    "suhu": 26.5,
    "kelembapan": 75.0
  }'
```

### Test API dengan JavaScript:

```javascript
fetch('http://localhost/sistem-kualitas-udara/api/post_data.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        lokasi: "Condongcatur",
        pm25: 38.5,
        pm10: 62.8,
        co: 1.7,
        no2: 0.06,
        so2: 0.05,
        o3: 0.04,
        suhu: 30.2,
        kelembapan: 69.5
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## 📊 Parameter Kualitas Udara

### Parameter yang Dipantau:

| Parameter | Satuan | Batas Aman | Keterangan |
|-----------|--------|------------|------------|
| PM2.5 | µg/m³ | ≤ 55.4 | Partikel debu berukuran 2.5 mikrometer |
| PM10 | µg/m³ | ≤ 154 | Partikel debu berukuran 10 mikrometer |
| CO | ppm | ≤ 4.0 | Carbon Monoxide (Karbon Monoksida) |
| NO₂ | ppm | ≤ 0.1 | Nitrogen Dioxide |
| SO₂ | ppm | ≤ 0.075 | Sulfur Dioxide |
| O₃ | ppm | ≤ 0.07 | Ozone |
| Suhu | °C | - | Temperature udara |
| Kelembapan | % | - | Humidity |

### Kategori AQI (Air Quality Index):

| Rentang AQI | Kategori | Keterangan |
|-------------|----------|------------|
| 0 - 50 | Baik | Kualitas udara sangat baik |
| 51 - 100 | Sedang | Dapat diterima |
| 101 - 150 | Tidak Sehat untuk Kelompok Sensitif | Perhatian khusus |
| 151 - 200 | Tidak Sehat | Mulai membahayakan |
| 201 - 300 | Sangat Tidak Sehat | Berbahaya |
| 301+ | Berbahaya | Darurat kesehatan |

---

## 🔧 Troubleshooting

### 1. Koneksi Database Gagal
**Problem:** "Koneksi database gagal"

**Solusi:**
- Pastikan MySQL sudah berjalan di XAMPP
- Cek username dan password di `koneksi.php`
- Pastikan database `kualitas_udara` sudah dibuat



### 3. Data Tidak Muncul
**Problem:** Halaman kosong atau tidak ada data

**Solusi:**
- Pastikan database sudah di-import dengan benar
- Cek apakah ada data dummy di tabel `sensor_data`
- Periksa error log PHP

### 4. API Tidak Berfungsi
**Problem:** POST data gagal

**Solusi:**
- Pastikan file `api/post_data.php` dapat diakses
- Cek format JSON request
- Periksa error log di browser console

---

## 📝 Catatan Penting

1. **Keamanan:**
   - Sistem ini untuk pembelajaran/development
   - Untuk production, tambahkan authentication dan validation lebih ketat
   - Gunakan prepared statements (sudah diimplementasikan)

2. **Performance:**
   - Untuk data besar, pertimbangkan pagination dan indexing
   - Gunakan caching untuk query yang sering digunakan

3. **Maintenance:**
   - Backup database secara berkala
   - Monitor disk space untuk tabel log

---

## 👥 Tim Pengembang

**Kelompok 3**
- Sistem ini dikembangkan sebagai proyek untuk Dinas Lingkungan Hidup
- Menggunakan teknologi modern dan best practices

---

## 📄 Lisensi

Proyek ini dikembangkan untuk keperluan pendidikan dan monitoring lingkungan.

---

## 📞 Kontak

Untuk pertanyaan atau dukungan:
- Email: info@dlh-jogja.go.id
- Website: Dinas Lingkungan Hidup Yogyakarta

---

## 🎯 Fitur Mendatang (Roadmap)

- [ ] Export data ke Excel/PDF
- [ ] Notifikasi email otomatis
- [ ] Integrasi dengan WhatsApp API
- [ ] Mobile app (Android/iOS)
- [ ] Machine Learning untuk prediksi
- [ ] Dashboard admin untuk manajemen user
- [ ] Peta interaktif Yogyakarta dengan marker sensor

---

**Terima kasih telah menggunakan Sistem Pemantauan Kualitas Udara!** 🌱

Untuk Yogyakarta yang lebih bersih dan sehat. 💚