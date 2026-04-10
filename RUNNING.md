# Running the Application

Dokumen ini menjelaskan cara menjalankan Backend (Laravel), Mobile (Flutter), dan ngrok untuk pengujian pada perangkat fisik.

## Prerequisites

Pastikan software berikut sudah terinstall:
- **Backend**: PHP 8.2+, Composer, PostgreSQL
- **Mobile**: Flutter SDK, Dart
- **Ngrok**: [Download ngrok](https://ngrok.com/download)

---

## 1. Menjalankan Backend (Laravel)

### Langkah-langkah:

```bash
# 1. Masuk ke direktori backend
cd backend

# 2. Install dependencies
composer install

# 3. Setup environment (jika belum ada .env)
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Setup database (pastikan PostgreSQL sudah running)
# Edit .env sesuai konfigurasi database Anda:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=attendance_app
# DB_USERNAME=postgres
# DB_PASSWORD=admin

# 6. Run migrations
php artisan migrate

# 7. Install Filament (jika belum)
php artisan filament:install

# 8. Create admin user
php artisan make:filament-user

# 9. Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000
```

### Akses:
- **API**: http://localhost:8000
- **Filament Admin**: http://localhost:8000/admin

---

## 2. Menjalankan Mobile (Flutter)

### Untuk Emulator Android:

```bash
# 1. Masuk ke direktori mobile
cd mobile

# 2. Install dependencies
flutter pub get

# 3. Generate code (freezed, json_serializable)
dart run build_runner build --delete-conflicting-outputs

# 4. Run app di emulator
flutter run
```

### Untuk Device Fisik (menggunakan ngrok):

Setelah menjalankan ngrok (lihat section 3), update API URL di mobile:

```bash
# Edit file: lib/core/network/api_client.dart
# Ganti baseUrl dengan URL ngrok Anda
```

Contoh:
```dart
static String get baseUrl {
  // Ganti dengan URL ngrok Anda
  return 'https://your-ngrok-url.ngrok-free.app';
}
```

```bash
# Connect device dengan USB debugging
flutter devices  # untuk melihat device yang tersedia

# Run di device fisik
flutter run -d <device_id>
```

---

## 3. Menjalankan Ngrok (untuk Device Fisik)

Ngrok diperlukan untuk mengakses localhost dari device fisik (HP asli).

### Langkah-langkah:

```bash
# 1. Extract ngrok
unzip ngrok.zip

# 2. Connect akun ngrok (sekali saja)
./ngrok authtoken YOUR_AUTH_TOKEN

# 3. Expose Laravel server
./ngrok http 8000
```

### Output пример:
```
Session Status                online
Account                       your@email.com
URL                           https://abc123.ngrok-free.app
```

### Setelah dapat URL ngrok:

1. **Update mobile** - Edit [api_client.dart](mobile/lib/core/network/api_client.dart):
   ```dart
   static String get baseUrl {
     return 'https://abc123.ngrok-free.app';
   }
   ```

2. **Update backend CORS** - Tambahkan URL ngrok di `config/cors.php` atau `.env`:
   ```
   CORS_ALLOWED_ORIGINS=https://abc123.ngrok-free.app
   ```

3. **Restart backend** setelah perubahan CORS

---

## 4. Cara Cepat (Simultan Running)

Buka 3 terminal terpisah:

**Terminal 1 - Backend:**
```bash
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

**Terminal 2 - Ngrok:**
```bash
./ngrok http 8000
```

**Terminal 3 - Mobile:**
```bash
cd mobile
flutter run -d <device_id>
```

---

## Troubleshooting

### Device tidak konek ke API:
- Pastikan ngrok sudah running
- Check URL ngrok sudah benar di api_client.dart
- Pastikan CORS di backend sudah benar
- Restart flutter app setelah ganti URL

### Database error:
- Pastikan PostgreSQL running
- Check credentials di .env
- Run `php artisan config:clear`

### Ngrok expired:
- Ngrok free hanya berlaku 2 jam
- Jalankan ulang `./ngrok http 8000`
- Update URL di mobile app

---

## Akun Dummy untuk Testing

Lihat [akun_dummy.md](akun_dummy.md) untuk credentials testing.
