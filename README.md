# Badar Net

Sistem Billing ISP & Manajemen MikroTik Hotspot — rewrite modern dari PHP (15+ tahun legacy PHP) ke arsitektur Laravel 13 + React 19 + Inertia.js v3.

## Fitur Utama

### Panel Admin
- **Dasbor** — Statistik pelanggan aktif, pendapatan harian/bulanan, transaksi terakhir
- **Manajemen Pelanggan** — CRUD pelanggan dengan pencarian, filter status, paginasi
- **Paket Layanan** — Hotspot, PPPoE, Balance dengan konfigurasi bandwidth, validitas, harga
- **Router MikroTik** — Tambah/edit router, test koneksi, status online/offline
- **Bandwidth & Pool IP** — Profil kecepatan dan IP pool untuk PPPoE
- **Isi Ulang** — Recharge pelanggan dengan metode manual, voucher, atau balance
- **Voucher** — Generate kode voucher massal, cetak dengan QR code
- **Transaksi** — Riwayat lengkap semua transaksi billing
- **Gateway Pembayaran** — Konfigurasi Midtrans, Xendit, Tripay, Manual
- **Audit Gateway** — Trail lengkap transaksi payment gateway
- **Laporan** — Laporan transaksi harian/periodik dengan filter
- **Log Aktivitas** — Sistem log dan log pengiriman pesan
- **Pesan** — Kirim pesan tunggal/massal via WhatsApp, SMS, Email, Inbox
- **Field Kustom** — Field dinamis untuk data pelanggan
- **Pengaturan** — Aplikasi, lokalisasi, user admin, database, maintenance mode

### Portal Pelanggan
- **Dasbor** — Info akun, paket aktif, saldo, transaksi terakhir
- **Paket** — Jelajahi paket yang tersedia
- **Transaksi** — Riwayat transaksi dan invoice
- **Profil** — Update data dan ubah password

### API
- Endpoint REST untuk integrasi pihak ketiga
- Webhook pembayaran (Midtrans, Xendit, Tripay)
- Autentikasi Sanctum

### Integrasi MikroTik
- Raw TCP RouterOS API client
- 4 device driver: Hotspot, PPPoE, Radius, Dummy
- Queue-based sync (non-blocking)
- Auto-sync saat recharge

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.3+) |
| Frontend | React 19 + TypeScript |
| Bridge | Inertia.js v3 |
| UI | shadcn/ui + TailwindCSS 4 |
| Build | Vite 8 |
| Database | MySQL/MariaDB |
| Auth | Laravel Session (dual guard) |
| API Auth | Laravel Sanctum |
| Icons | Lucide React |
| Dark Mode | next-themes (light/dark/system) |

## Instalasi

### Prasyarat
- PHP 8.3+
- MySQL/MariaDB
- Node.js 18+
- Composer 2.x

### Setup

```bash
# Clone repository
git clone https://github.com/mickirr-create/isp-badar-net.git
cd isp-badar-net

# Install dependencies PHP
composer install

# Install dependencies JS
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Edit .env sesuai konfigurasi database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=phpnuxbill
DB_USERNAME=root
DB_PASSWORD=

# Jalankan migrasi dan seed
php artisan migrate
php artisan db:seed

# Build frontend
npm run build

# Jalankan server
php artisan serve
```

### Login Default

| User | Password | Role |
|------|----------|------|
| `admin` | `admin` | SuperAdmin |

Akses: `http://localhost:8000/admin`

## Struktur Proyek

```
isp-badar-net/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # 16 controller admin
│   │   ├── Customer/       # 4 controller pelanggan
│   │   └── Api/            # 3 controller API
│   ├── Models/             # 21 model Eloquent
│   ├── Services/
│   │   ├── Network/        # MikroTik + 4 device driver
│   │   └── Payment/        # 4 payment gateway
│   └── Jobs/               # 3 queue job MikroTik sync
├── database/migrations/    # 26 migrasi database
├── resources/js/
│   ├── components/ui/      # 16 komponen shadcn/ui
│   ├── layouts/            # Sidebar layout dengan dark mode
│   └── pages/
│       ├── Admin/          # 30+ halaman admin
│       └── Customer/       # 5 halaman pelanggan
├── routes/
│   ├── web.php             # 102 rute
│   └── api.php             # Rute API
└── vite.config.js
```

## Database

26 tabel dengan prefix `tbl_`:

| Tabel | Deskripsi |
|-------|-----------|
| `tbl_users` | Akun admin/staf |
| `tbl_customers` | Akun pelanggan |
| `tbl_plans` | Paket internet |
| `tbl_bandwidth` | Profil bandwidth |
| `tbl_routers` | Router MikroTik |
| `tbl_pool` | IP pool PPPoE |
| `tbl_user_recharges` | Langganan aktif |
| `tbl_transactions` | Riwayat billing |
| `tbl_voucher` | Kode voucher |
| `tbl_payment_gateway` | Transaksi gateway |
| `tbl_appconfig` | Pengaturan aplikasi |
| `tbl_logs` | Log aktivitas |
| `tbl_message_logs` | Log pengiriman pesan |
| ... | dll |

## API Endpoints

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/auth/login` | POST | Login |
| `/api/auth/logout` | POST | Logout |
| `/api/customer/me` | GET | Info pelanggan |
| `/api/customer/balance` | GET | Saldo |
| `/api/customer/active-plan` | GET | Paket aktif |
| `/api/customer/plans` | GET | Daftar paket |
| `/api/customer/transactions` | GET | Riwayat transaksi |
| `/api/callback/midtrans` | POST | Webhook Midtrans |
| `/api/callback/xendit` | POST | Webhook Xendit |
| `/api/callback/tripay` | POST | Webhook Tripay |

## Screenshot

> Screenshot dapat ditambahkan setelah aplikasi berjalan.

## Kontribusi

1. Fork repository
2. Buat branch (`git checkout -b feature/nama-fitur`)
3. Commit (`git commit -m 'Tambah fitur'`)
4. Push (`git push origin feature/nama-fitur`)
5. Buka Pull Request

## Lisensi

MIT License

## Credits

- [PHPNuxBill](https://github.com/hotspotbilling/phpnuxbill) — Aplikasi original
- [Laravel](https://laravel.com) — Backend framework
- [React](https://react.dev) — Frontend library
- [Inertia.js](https://inertiajs.com) — Bridge Laravel-React
- [shadcn/ui](https://ui.shadcn.com) — Komponen UI
- [Tailwind CSS](https://tailwindcss.com) — Utility-first CSS
