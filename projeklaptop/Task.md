# TASK EXECUTION ROADMAP
## Sistem Informasi E-Commerce Toko Laptop Baru dan Bekas
**Dokumen Referensi:** [PRD v1.0](file:///c:/xampp/htdocs/dunia-laptop/projeklaptop/prdlaptop.md)  
**Status Keseluruhan:** In Progress  
**Framework:** Laravel 12 | **Database:** MySQL (DB Facade / Query Builder) | **Styling:** Tailwind CSS + Alpine.js

---

## 📌 Ringkasan Prinsip Arsitektur Wajib
1. **Query Database**: Wajib menggunakan **Laravel Query Builder (`DB` facade)** sebagai pendekatan utama untuk semua query bisnis (katalog, keranjang, pesanan, chatbot handler, laporan penjualan).
2. **Integritas Transaksi**: Wajib menggunakan `DB::transaction()` dan pessimistic locking (`lockForUpdate()`) saat checkout untuk mencegah *race condition* dan stok negatif (FR-18).
3. **Snapshot Pesanan**: Menyimpan data statis saat transaksi (`product_name`, `product_sku`, `unit_price`, `quantity`, `snapshot_specs`) ke `order_items` (FR-16).
4. **Keamanan Pembayaran**: Kredensial Server Key Midtrans hanya di backend; notifikasi webhook wajib diverifikasi SHA512 signature secara idempoten (FR-21 s/d FR-25).
5. **Chatbot Real-Time**: Chatbot berbasis template pertanyaan dengan *dynamic handlers* yang mengambil harga dan stok aktual langsung dari database (FR-40, Bab 6).
6. **Otorisasi Ketat**: Pembatasan hak akses berbasis role (Super Admin, Admin, Staff, Customer) diterapkan di backend melalui middleware (FR-03, FR-04).

---

## 📋 DAFTAR TUGAS EKSEKUSI

### 🚀 FASE 1: Fondasi, Package & Lingkungan Pengembangan
- [x] **1.1. Inisialisasi Git & Perlindungan Secret**
  - [x] Inisialisasi repositori Git lokal (`git init`).
  - [x] Pastikan `.gitignore` memuat `.env`, `node_modules`, `/vendor`, dan file storage privat.
- [x] **1.2. Instalasi Package yang Dibutuhkan**
  - [x] Install `laravel/socialite` untuk login Google.
  - [x] Pasang helper / client library untuk Midtrans Snap API.
- [x] **1.3. Konfigurasi Environment & Services**
  - [x] Daftarkan konfigurasi `google` dan `midtrans` di `config/services.php`.
  - [x] Perbarui `.env.example` dengan variabel `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION`, `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`.

---

### 🔐 FASE 2: Autentikasi & Role-Based Access Control (RBAC)
- [x] **2.1. Middleware Hak Akses Role**
  - [x] Buat `app/Http/Middleware/RoleMiddleware.php` untuk memvalidasi akses peran pengguna (`super_admin`, `admin`, `staff`, `customer`).
  - [x] Daftarkan alias middleware `role` di `bootstrap/app.php`.
- [x] **2.2. Penyesuaian Alur Registrasi & Login Standar**
  - [x] Pastikan pendaftaran akun baru melalui `RegisteredUserController` secara otomatis menetapkan role `customer`.
  - [x] Konfigurasi alur pengalihan login (*login redirect*): Super Admin / Admin / Staff diarahkan ke dashboard operasional, sedangkan Customer diarahkan ke halaman utama / akun.
- [x] **2.3. Autentikasi Google OAuth (Laravel Socialite)**
  - [x] Buat `app/Http/Controllers/Auth/GoogleAuthController.php` (fungsi `redirectToGoogle` dan `handleGoogleCallback`).
  - [x] Tangani pembuatan akun baru dengan Google ID, email terverifikasi, dan role `customer`, atau login akun yang sudah ada.
  - [x] Tambahkan tombol "Masuk dengan Google" pada halaman login dan register (`resources/views/auth/login.blade.php` & `register.blade.php`).

---

### 💻 FASE 3: Homepage Dinamis & Katalog Produk (DB Facade)
- [x] **3.1. Service Query Produk & Katalog**
  - [x] Buat `app/Services/Catalog/ProductQueryService.php` menggunakan `DB::table('products')`.
  - [x] Implementasikan query daftar produk dengan join ke `categories`, `brands`, dan gambar utama `product_images`.
  - [x] Tambahkan fungsionalitas pencarian nama/SKU/deskripsi laptop.
  - [x] Tambahkan filter multi-kriteria:
    - Kondisi: Baru (`new`) vs Bekas (`used`).
    - Kategori & Brand.
    - Rentang harga minimum & maksimum.
    - Status publikasi (`published`) dan ketersediaan stok (`stock > 0`).
  - [x] Implementasikan pengurutan (Terbaru, Harga Termurah, Harga Termahal).
- [x] **3.2. Halaman Beranda Dinamis (Storefront Homepage)**
  - [x] Buat `app/Http/Controllers/Store/HomeController.php`.
  - [x] Query data sections aktif dari `homepage_sections` sesuai `sort_order` dan pengaturan `site_settings`.
  - [x] Buat layout storefront `resources/views/layouts/storefront.blade.php` dengan navbar, tema toko dinamis, dan footer.
  - [x] Buat view `resources/views/store/home.blade.php` merender banner hero, kategori pilihan, laptop unggulan, produk terbaru, dan FAQ.
- [x] **3.3. Halaman Katalog & Detail Produk**
  - [x] Buat `app/Http/Controllers/Store/ProductCatalogController.php`.
  - [x] Buat view katalog `resources/views/store/catalog/index.blade.php` dengan filter sidebar interaktif dan grid kartu produk.
  - [x] Buat view detail laptop `resources/views/store/catalog/show.blade.php`:
    - Galeri foto produk (multi-angle).
    - Badge kondisi (Baru / Bekas) dengan rincian kondisi fisik jika bekas.
    - Tabel spesifikasi lengkap (Processor, RAM, Storage, GPU, Display, Baterai, OS, Garansi).
    - Status stok dan tombol aksi "Tambah ke Keranjang" & "Beli Sekarang".

---

### 🛒 FASE 4: Keranjang Belanja, Alamat, & Checkout Transaksional
- [ ] **4.1. Manajemen Keranjang Belanja**
  - [ ] Buat `app/Services/Cart/CartService.php` menggunakan `DB` facade pada tabel `carts` dan `cart_items`.
  - [ ] Buat `app/Http/Controllers/Store/CartController.php` (Tambah produk, ubah jumlah kuantitas, hapus item, kosongkan keranjang).
  - [ ] Buat view keranjang `resources/views/store/cart/index.blade.php` dengan rincian subtotal dan validasi stok realtime.
- [ ] **4.2. Manajemen Alamat Pengiriman**
  - [ ] Buat `app/Http/Controllers/Store/AddressController.php` untuk mengelola alamat pelanggan di tabel `addresses`.
  - [ ] Fitur pilih alamat default, tambah alamat baru, dan form edit alamat.
- [ ] **4.3. Layanan Checkout & Penguncian Stok (Order Concurrency)**
  - [ ] Buat `app/Services/Order/OrderService.php`.
  - [ ] Implementasikan alur `createOrderFromCart()` di dalam `DB::transaction()`:
    1. Kunci baris stok produk dengan `lockForUpdate()`.
    2. Validasi ulang ketersediaan stok setiap item.
    3. Kurangi stok produk secara atomik (`decrement`).
    4. Simpan header `orders` (nomor pesanan unik, subtotal, ongkos kirim, diskon, total pembayaran, status `pending`).
    5. Simpan snapshot produk ke `order_items` (`product_name`, `product_sku`, `unit_price`, `quantity`, `subtotal`, `snapshot_specs`).
    6. Buat record awal pengiriman pada tabel `shipments` (kurir, alamat lengkap tujuan, status `pending`).
    7. Bersihkan item keranjang belanja.
- [ ] **4.4. Halaman Checkout**
  - [ ] Buat `app/Http/Controllers/Store/CheckoutController.php`.
  - [ ] Buat view checkout `resources/views/store/checkout/index.blade.php` menampilkan ringkasan pesanan, pemilihan alamat, pilihan kurir, dan rincian biaya.

---

### 💳 FASE 5: Integrasi Pembayaran Midtrans Snap & Webhook Idempoten
- [ ] **5.1. Midtrans Service & Snap Token Generator**
  - [ ] Buat `app/Services/Payment/MidtransService.php`.
  - [ ] Implementasikan metode `createSnapTransaction(Order $order)` untuk memanggil API Midtrans Snap dengan parameter pesanan, rincian item, dan data pelanggan.
  - [ ] Simpan token pembayaran dan data referensi ke tabel `payments`.
- [ ] **5.2. Halaman Pembayaran & Midtrans Snap Modal**
  - [ ] Integrasikan script Snap JS (`app.sandbox.midtrans.com/snap/snap.js`) pada halaman review pembayaran pesanan.
  - [ ] Munculkan popup Snap otomatis atau via tombol "Bayar Sekarang".
  - [ ] Buat halaman callback status sementara (Finish / Unfinish / Error URL).
- [ ] **5.3. Endpoint Webhook / Notifikasi Midtrans Resmi**
  - [ ] Buat `app/Http/Controllers/Payment/MidtransWebhookController.php` dengan endpoint `POST /midtrans/notification`.
  - [ ] Kecualikan route ini dari CSRF token verification di `bootstrap/app.php`.
  - [ ] **Validasi SHA512 Signature**: Verifikasi kecocokan hash `hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey)`.
  - [ ] **Penanganan Status Idempoten**:
    - Status `settlement` atau `capture` (accept) -> Update pembayaran ke `settlement`, update pesanan ke `processing`.
    - Status `pending` -> Update pembayaran ke `pending`.
    - Status `deny`, `expire`, atau `cancel` -> Update status pesanan ke `cancelled`, status pembayaran ke `cancel`/`expire`, **serta kembalikan stok produk ke database secara otomatis**.
  - [ ] Kembalikan respons HTTP `200 OK` ke server Midtrans.

---

### 📦 FASE 6: Akun Pelanggan & Riwayat Pesanan
- [ ] **6.1. Halaman Riwayat Pesanan Pelanggan**
  - [ ] Buat `app/Http/Controllers/Store/CustomerOrderController.php`.
  - [ ] Buat view daftar pesanan `resources/views/store/orders/index.blade.php` (menampilkan nomor pesanan, tanggal, total, status pembayaran & pengiriman).
  - [ ] Buat view rincian pesanan `resources/views/store/orders/show.blade.php`:
    - Rincian item laptop yang dibeli (snapshot harga & spesifikasi).
    - Status pembayaran (termasuk tombol bayar ulang jika masih `pending`).
    - Status pengiriman dan pelacakan nomor resi.
    - Tombol batalkan pesanan jika pesanan belum dibayar.

---

### 📊 FASE 7: Dashboard Operasional & Laporan Penjualan (Admin & Staff)
- [ ] **7.1. Layout & Dashboard Ringkasan**
  - [ ] Buat layout panel admin `resources/views/layouts/admin.blade.php` dengan navigasi berdasarkan hak akses role.
  - [ ] Buat `app/Http/Controllers/Admin/AdminDashboardController.php`.
  - [ ] Buat view `resources/views/admin/dashboard.blade.php` menampilkan ringkasan metrik (Total Penjualan Valid, Pesanan Baru, Stok Menipis, Ringkasan Transaksi).
- [ ] **7.2. Manajemen Pesanan & Pengiriman (Role: Admin)**
  - [ ] Buat `app/Http/Controllers/Admin/OrderManagementController.php`.
  - [ ] Halaman daftar seluruh pesanan dengan filter status (`pending`, `processing`, `shipped`, `completed`, `cancelled`).
  - [ ] Form rincian pesanan & update status pesanan.
  - [ ] Input kurir, nomor resi pengiriman, serta pembaruan status shipment (`picked_up`, `in_transit`, `delivered`).
- [ ] **7.3. Laporan Penjualan (Role: Admin & Super Admin)**
  - [ ] Buat `app/Http/Controllers/Admin/SalesReportController.php`.
  - [ ] Filter transaksi berdasarkan rentang tanggal (`start_date` s/d `end_date`) dan status.
  - [ ] Agregasi metrik akurat via Query Builder: Total omzet pembayaran berhasil (`settlement`), kuantitas produk terjual, dan daftar laptop terlaris.
  - [ ] Tampilan laporan siap cetak / ekspor data ringkasan.
- [ ] **7.4. Manajemen Produk Laptop (Role: Staff & Admin)**
  - [ ] Buat `app/Http/Controllers/Admin/ProductManagementController.php`.
  - [ ] Form tambah & edit laptop (nama, brand, kategori, SKU, kondisi Baru/Bekas, deskripsi detail).
  - [ ] Form spesifikasi teknis lengkap (CPU, GPU, RAM, Storage, Layar, Baterai, Port, Garansi).
  - [ ] Manajemen galeri gambar produk (upload gambar, hapus foto, set foto utama).
  - [ ] Pembaruan cepat stok dan harga laptop.

---

### 🤖 FASE 8: Chatbot Template Interaktif & Dynamic Live Data
- [ ] **8.1. Service Chatbot & Dynamic Handlers**
  - [ ] Buat `app/Services/Chatbot/ChatbotService.php`.
  - [ ] Implementasikan antarmuka handler respons chatbot `App\Services\Chatbot\Handlers\ChatbotHandlerInterface`.
  - [ ] Buat handler data langsung via `DB` facade:
    - `CheckProductStockHandler`: Mengambil stok dan ketersediaan laptop terkini dari database.
    - `RecommendLaptopHandler`: Mengambil laptop berdasarkan kriteria budget / kategori terlaris.
    - `TrackCustomerOrderHandler`: Memberikan status pesanan terkini pelanggan yang sedang login.
  - [ ] Fallback response handler untuk pertanyaan yang tidak dikenali dengan opsi kontak toko / WhatsApp.
- [ ] **8.2. Widget Chatbot pada Storefront**
  - [ ] Buat komponen Blade `resources/views/components/chatbot-widget.blade.php` (floating interactive chat bubble dengan Tailwind & Alpine.js).
  - [ ] Buat endpoint API chatbot `app/Http/Controllers/Store/ChatbotController.php` (`/api/chatbot/options` dan `/api/chatbot/ask`).
  - [ ] Tampilkan daftar kategori pertanyaan dan daftar pertanyaan template aktif.
  - [ ] Tampilkan jawaban instan (statis maupun dinamis berbasis database riil).
- [ ] **8.3. Manajemen Template Chatbot (Role: Super Admin)**
  - [ ] Buat `app/Http/Controllers/Admin/ChatbotTemplateController.php`.
  - [ ] View daftar template chatbot dengan indikator kategori, tipe jawaban (Statis / Dinamis), dan status aktif.
  - [ ] Form tambah & edit template (pertanyaan, kategori, tipe respons, teks jawaban statis, pilihan handler dinamis, urutan tampilan `sort_order`).

---

### ⚙️ FASE 9: Pengaturan Website & Homepage Sections Manager (Super Admin)
- [ ] **9.1. Pengaturan Toko & Tampilan (Site Settings)**
  - [ ] Buat `app/Http/Controllers/Admin/SiteSettingController.php`.
  - [ ] Form pengelolaan identitas toko (nama toko, slogan, logo, kontak telepon/WhatsApp, alamat toko fisik, tautan medsos).
  - [ ] Pengaturan warna tema website yang dapat disesuaikan.
- [ ] **9.2. Homepage Section Manager**
  - [ ] Buat `app/Http/Controllers/Admin/HomepageSectionController.php`.
  - [ ] Pengaturan visibilitas section beranda (Hero Banner, Kategori Pilihan, Laptop Unggulan, Laptop Bekas Pilihan, FAQ, Footer Info).
  - [ ] Pengaturan urutan urut (*sort order*) section homepage.
- [ ] **9.3. Manajemen Akun Pengguna & Role (Super Admin)**
  - [ ] Buat `app/Http/Controllers/Admin/UserManagementController.php`.
  - [ ] Kelola daftar akun pengguna dan ubah hak akses peran (Super Admin, Admin, Staff, Customer).

---

### 🧪 FASE 10: Pengujian Menyeluruh, Verifikasi & Finalisasi
- [ ] **10.1. Automated Feature Testing (PHPUnit / Pest)**
  - [ ] `tests/Feature/AuthAndRoleTest.php`: Tes registrasi, login, dan proteksi RoleMiddleware.
  - [ ] `tests/Feature/ProductCatalogTest.php`: Tes pencarian, filter kondisi baru/bekas, dan query detail.
  - [ ] `tests/Feature/OrderCheckoutTest.php`: Tes DB transaction locking, snapshot item, dan integritas stok.
  - [ ] `tests/Feature/MidtransWebhookTest.php`: Tes verifikasi signature SHA512 dan rollback stok saat dibatalkan.
  - [ ] `tests/Feature/ChatbotEngineTest.php`: Tes respons statis dan handler dinamis data stok riil.
- [ ] **10.2. Verifikasi Manual Komprehensif**
  - [ ] Uji alur pelanggan: Buka web -> Tanya chatbot stok -> Masukkan keranjang -> Checkout -> Pembayaran -> Cek riwayat pesanan.
  - [ ] Uji alur Staff: Tambah laptop baru dan laptop bekas -> Cek foto dan spesifikasi muncul di katalog.
  - [ ] Uji alur Admin: Proses pesanan masuk -> Input nomor resi pengiriman -> Validasi laporan penjualan tanggal tertentu.
  - [ ] Uji alur Super Admin: Ubah konfigurasi section beranda -> Tambah template chatbot -> Kelola pengguna.
- [ ] **10.3. Dokumentasi & Panduan Operasional**
  - [ ] Buat artifact walkthrough komprehensif yang mendokumentasikan hasil implementasi dan pengujian sistem.

