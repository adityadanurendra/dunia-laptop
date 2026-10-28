# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## Sistem Informasi E-Commerce Toko Laptop Baru dan Bekas

**Versi:** 1.0  
**Platform:** Website  
**Framework:** Laravel 12  
**Database:** MySQL  
**Target pengguna:** Pelanggan, Super Admin, Admin, dan Staff

---

# 1. Latar Belakang

Toko laptop yang menjual produk baru dan bekas membutuhkan website e-commerce untuk mempermudah pelanggan melihat katalog, memperoleh informasi produk, melakukan pemesanan, membayar secara online, dan memantau status pesanan.

Website juga harus membantu pemilik toko mengelola produk dan operasional melalui dashboard. Untuk mengurangi waktu yang dihabiskan dalam menjawab pertanyaan berulang, sistem menyediakan chatbot berbasis pilihan pertanyaan yang sudah ditentukan sebelumnya.

Selain fungsi transaksi, Super Admin dapat mengelola konten dan komponen tampilan website melalui dashboard tanpa harus mengubah kode program secara langsung.

# 2. Tujuan Produk

1. Menyediakan katalog laptop baru dan bekas secara online.
2. Mempermudah pelanggan mencari dan membandingkan informasi produk.
3. Menyediakan proses keranjang, checkout, pembayaran, dan pemantauan pesanan.
4. Mempermudah pengelolaan produk, stok, transaksi, dan laporan.
5. Mengurangi pertanyaan berulang yang harus dijawab manual oleh pemilik toko melalui chatbot otomatis.
6. Memungkinkan pengelolaan konten dan section homepage melalui dashboard.
7. Menerapkan pembagian hak akses berdasarkan peran pengguna.

# 3. Ruang Lingkup

## 3.1 Fitur yang termasuk dalam versi pertama

- Registrasi dan login.
- Login menggunakan Google.
- Manajemen role dan hak akses.
- Katalog laptop baru dan bekas.
- Pencarian dan filter produk.
- Detail produk dan spesifikasi.
- Keranjang dan checkout.
- Integrasi pembayaran Midtrans Snap.
- Manajemen pesanan dan status pengiriman.
- Riwayat pesanan pelanggan.
- Dashboard pengelolaan toko.
- Laporan penjualan.
- Chatbot berbasis pilihan pertanyaan dan jawaban otomatis.
- Pengaturan konten serta tampilan homepage oleh Super Admin.

## 3.2 Di luar ruang lingkup versi pertama

- Chatbot AI generatif yang bebas menjawab semua pertanyaan.
- Sistem rekomendasi laptop berbasis machine learning.
- Editor website dengan drag-and-drop bebas seperti website builder profesional.
- Integrasi otomatis ke seluruh layanan kurir melalui API, kecuali kemudian ditetapkan sebagai kebutuhan.
- Aplikasi mobile native.

# 4. Jenis Pengguna dan Hak Akses

## 4.1 Pelanggan

Pelanggan dapat:
- Melihat dan mencari produk.
- Memfilter laptop baru atau bekas.
- Melihat spesifikasi, harga, stok, dan gambar.
- Menggunakan chatbot.
- Mengelola alamat pengiriman.
- Menambahkan produk ke keranjang.
- Melakukan checkout dan pembayaran.
- Melihat riwayat serta status pesanan.

## 4.2 Super Admin

Super Admin memiliki kendali tertinggi terhadap sistem dan dapat:
- Mengelola akun pengguna dan role.
- Mengelola pengaturan website.
- Mengubah banner, teks, gambar, tautan, warna tema, dan urutan section homepage.
- Mengaktifkan atau menonaktifkan section homepage yang telah disediakan.
- Mengelola template pertanyaan dan jawaban chatbot.
- Mengakses fitur operasional sesuai kebijakan sistem.

## 4.3 Admin

Admin bertanggung jawab terhadap operasional toko dan dapat:
- Melihat dan memproses pesanan.
- Memantau pembayaran.
- Mengelola status pesanan dan pengiriman.
- Memantau stok.
- Melihat laporan penjualan.
- Mengelola visibilitas produk sesuai kebijakan operasional.

Admin tidak dapat mengubah konfigurasi sistem yang dikhususkan bagi Super Admin.

## 4.4 Staff

Staff bertanggung jawab terhadap pengelolaan data produk dan dapat:
- Menambahkan produk.
- Mengubah nama, deskripsi, harga, kondisi, spesifikasi, dan foto.
- Memperbarui stok.
- Melihat data produk yang dikelolanya.

Staff tidak dapat mengelola role pengguna, konfigurasi Midtrans, atau pengaturan tampilan website.

Seluruh pembatasan hak akses harus diterapkan di backend, bukan hanya dengan menyembunyikan tombol pada antarmuka.

# 5. Kebutuhan Fungsional

## 5.1 Autentikasi

**FR-01:** Sistem menyediakan registrasi dan login menggunakan email serta kata sandi.

**FR-02:** Sistem menyediakan login Google melalui Laravel Socialite.

**FR-03:** Sistem menentukan hak akses pengguna berdasarkan role.

**FR-04:** Sistem menyediakan logout dan validasi akses halaman.

## 5.2 Katalog Produk

**FR-05:** Sistem menampilkan daftar laptop yang tersedia.

**FR-06:** Sistem membedakan produk baru dan bekas.

**FR-07:** Sistem menyediakan pencarian berdasarkan nama atau informasi produk.

**FR-08:** Sistem menyediakan filter berdasarkan kondisi, kategori, merek, dan rentang harga.

**FR-09:** Halaman detail produk menampilkan foto, deskripsi, harga, stok, kondisi, dan spesifikasi.

**FR-10:** Produk yang tidak aktif tidak ditampilkan kepada pelanggan.

**FR-11:** Sistem membedakan stok tersedia, habis, dan status publikasi produk.

## 5.3 Keranjang dan Checkout

**FR-12:** Pelanggan dapat menambahkan produk ke keranjang.

**FR-13:** Pelanggan dapat mengubah kuantitas atau menghapus item keranjang.

**FR-14:** Sistem memvalidasi stok dan harga terbaru sebelum pesanan dibuat.

**FR-15:** Sistem menghitung subtotal, biaya pengiriman, diskon jika tersedia, dan total pembayaran di backend.

**FR-16:** Sistem menyimpan snapshot nama produk, SKU, harga satuan, dan kuantitas dalam detail pesanan.

**FR-17:** Sistem menyimpan informasi pesanan dan alamat tujuan.

**FR-18:** Sistem mencegah stok menjadi negatif akibat transaksi yang berlangsung bersamaan.

## 5.4 Pembayaran Midtrans

**FR-19:** Sistem membuat transaksi pembayaran menggunakan Midtrans Snap.

**FR-20:** Sistem menampilkan antarmuka pembayaran Midtrans kepada pelanggan.

**FR-21:** Sistem menerima notifikasi pembayaran melalui endpoint backend.

**FR-22:** Sistem memverifikasi notifikasi menggunakan mekanisme resmi Midtrans sebelum memperbarui status pembayaran.

**FR-23:** Sistem menangani status pending, berhasil, gagal, kedaluwarsa, dan pembatalan sesuai status yang dilaporkan penyedia pembayaran.

**FR-24:** Sistem tidak menganggap pelanggan telah membayar hanya karena kembali ke halaman sukses.

**FR-25:** Sistem mencegah pemrosesan notifikasi berulang menghasilkan perubahan transaksi yang tidak konsisten.

Server Key Midtrans harus disimpan di environment backend dan tidak boleh dikirim ke browser.

## 5.5 Manajemen Pesanan dan Pengiriman

**FR-26:** Admin dapat melihat daftar pesanan.

**FR-27:** Admin dapat memperbarui status pemrosesan pesanan sesuai aturan bisnis.

**FR-28:** Sistem mendukung pencatatan metode pengiriman, biaya, nomor resi jika tersedia, dan status pengiriman.

**FR-29:** Pelanggan dapat melihat status pesanan melalui riwayat pembelian.

**FR-30:** Sistem memisahkan status pesanan, pembayaran, dan pengiriman.

**FR-31:** Sistem menyediakan mekanisme pembatalan dan pengembalian stok sesuai kondisi pesanan yang diperbolehkan.

## 5.6 Dashboard dan Laporan

**FR-32:** Admin dapat melihat ringkasan pesanan dan penjualan.

**FR-33:** Admin dapat melihat laporan berdasarkan rentang tanggal.

**FR-34:** Sistem menampilkan jumlah transaksi, total penjualan, dan produk terjual berdasarkan data transaksi yang valid.

**FR-35:** Sistem membedakan transaksi berhasil dari transaksi yang masih pending atau gagal.

## 5.7 Pengaturan Website

**FR-36:** Super Admin dapat mengubah konten homepage melalui dashboard.

**FR-37:** Super Admin dapat mengubah teks, gambar, tautan, dan warna tema yang diizinkan.

**FR-38:** Super Admin dapat mengatur urutan dan visibilitas section homepage.

**FR-39:** Sistem menggunakan komponen tampilan yang sudah ditentukan, seperti banner, kategori, produk unggulan, produk terbaru, FAQ, dan footer.

**FR-40:** Sistem memvalidasi data konfigurasi sebelum menyimpan perubahan.

**FR-41:** Perubahan konfigurasi tidak boleh memungkinkan eksekusi kode bebas dari input pengguna.

# 6. Spesifikasi Chatbot

## 6.1 Tujuan Chatbot

Chatbot dibuat untuk **mengurangi kebutuhan pemilik toko menjawab pertanyaan yang sering diajukan pelanggan secara manual**.

Chatbot bukan chatbot AI generatif. Versi pertama menggunakan daftar pilihan pertanyaan yang telah disiapkan dan jawaban otomatis yang dikelola melalui dashboard.

## 6.2 Mekanisme Chatbot

1. Pelanggan membuka widget chatbot pada website.
2. Sistem menampilkan pesan pembuka dan daftar pilihan pertanyaan.
3. Pelanggan memilih salah satu pertanyaan yang tersedia.
4. Sistem mengidentifikasi kategori pertanyaan.
5. Sistem memberikan jawaban otomatis berdasarkan template yang aktif.
6. Jika jawaban membutuhkan informasi produk, harga, atau stok, sistem mengambil informasi terkini dari database.
7. Pelanggan dapat memilih pertanyaan lain atau mengikuti opsi lanjutan yang disediakan.

Pelanggan tidak perlu menunggu pemilik toko membalas untuk memperoleh jawaban atas pertanyaan yang sudah didukung sistem.

## 6.3 Contoh Pilihan Pertanyaan

### A. Informasi produk
- Bagaimana cara melihat spesifikasi laptop?
- Apa perbedaan laptop baru dan bekas?
- Apakah produk masih tersedia?
- Bagaimana kondisi laptop bekas yang dijual?

### B. Harga dan stok
- Berapa harga laptop ini?
- Apakah laptop ini masih tersedia?
- Bagaimana cara mengetahui stok produk?

Jawaban terkait harga dan stok harus mengambil data produk aktual. Chatbot tidak boleh menampilkan harga atau stok yang sudah kedaluwarsa dari template statis.

### C. Pemesanan dan pembayaran
- Bagaimana cara membeli laptop?
- Bagaimana cara melakukan pembayaran?
- Metode pembayaran apa yang tersedia?
- Bagaimana cara mengetahui status pembayaran?

Jawaban harus sesuai dengan alur checkout dan konfigurasi pembayaran yang benar-benar diterapkan.

### D. Pengiriman dan pesanan
- Bagaimana cara mengetahui status pesanan?
- Bagaimana cara mengetahui status pengiriman?
- Di mana saya dapat melihat riwayat pesanan?

Informasi pesanan pribadi hanya boleh diberikan setelah sistem memverifikasi identitas dan hak akses pelanggan.

### E. Pertanyaan umum
- Bagaimana cara menghubungi toko?
- Bagaimana cara menggunakan website?
- Bagaimana jika pertanyaan saya tidak tersedia?

Informasi kontak harus berasal dari konfigurasi toko yang dikelola pihak berwenang. Jika pertanyaan tidak tersedia, chatbot menampilkan opsi kontak toko atau instruksi untuk menghubungi admin.

## 6.4 Pengelolaan Template Chatbot

Super Admin dapat:
- Menambah template pertanyaan.
- Mengubah teks pertanyaan dan jawaban.
- Mengelompokkan pertanyaan berdasarkan kategori.
- Mengaktifkan atau menonaktifkan template.
- Mengatur urutan pilihan pertanyaan.
- Melihat pratinjau jawaban.

Template sebaiknya memiliki identitas unik, teks pertanyaan, kategori, tipe jawaban, isi jawaban jika berupa teks statis, status aktif, urutan tampilan, serta timestamp.

Untuk jawaban dinamis, gunakan tipe respons atau handler yang ditentukan oleh aplikasi, bukan menjalankan kode yang disimpan dalam database.

## 6.5 Batasan Chatbot

- Chatbot tidak menghasilkan jawaban bebas menggunakan AI pada versi pertama.
- Chatbot hanya menjawab pertanyaan yang telah didukung oleh pilihan dan handler yang tersedia.
- Chatbot tidak boleh mengarang harga, stok, kebijakan pembayaran, atau status pesanan.
- Chatbot tidak boleh menampilkan informasi pesanan pelanggan lain.
- Pertanyaan di luar cakupan diarahkan ke kontak toko atau admin.
- Chatbot tidak menggantikan fungsi checkout, pembayaran, atau pengelolaan pesanan.

## 6.6 Kriteria Penerimaan Chatbot

Chatbot dinyatakan memenuhi kebutuhan jika:
1. Pilihan pertanyaan aktif tampil di widget.
2. Setiap pilihan menghasilkan jawaban yang sesuai.
3. Perubahan template oleh Super Admin tercermin pada chatbot.
4. Jawaban harga dan stok menggunakan data aktual.
5. Template nonaktif tidak ditampilkan.
6. Pertanyaan di luar cakupan menghasilkan respons fallback.
7. Informasi pribadi dan pesanan terlindungi oleh autentikasi serta otorisasi.
8. Chatbot dapat digunakan tanpa memerlukan pemilik toko menjawab pertanyaan template secara manual.

# 7. Kebutuhan Database

Gunakan MySQL dengan Laravel Migrations dan Laravel Query Builder melalui `DB` facade sebagai pendekatan utama untuk operasi database. Jangan menggunakan Eloquent ORM sebagai pendekatan utama.

Entitas inti yang perlu dirancang:
- `users`
- `addresses`
- `categories`
- `brands`
- `products`
- `product_images`
- `product_specifications` jika sesuai dengan rancangan spesifikasi
- `carts`
- `cart_items`
- `orders`
- `order_items`
- `payments`
- `shipments`
- `site_settings`
- `homepage_sections`
- `chatbot_templates`

Tabel tambahan hanya dibuat jika diperlukan oleh kebutuhan nyata.

Gunakan foreign key, indeks, unique constraint, transaksi database, dan parameter binding sesuai kebutuhan. Untuk pesanan, simpan snapshot produk dan harga. Jangan mengandalkan relasi Eloquent.

# 8. Kebutuhan Nonfungsional

**NFR-01 — Keamanan:** gunakan autentikasi, otorisasi, CSRF protection, validasi server-side, dan pencegahan SQL injection.

**NFR-02 — Integritas transaksi:** stok, pembayaran, dan pesanan harus konsisten walaupun terjadi permintaan bersamaan.

**NFR-03 — Kemudahan pemeliharaan:** kode terstruktur, penamaan konsisten, serta dokumentasi untuk keputusan teknis penting.

**NFR-04 — Responsivitas:** antarmuka dapat digunakan pada desktop dan perangkat mobile.

**NFR-05 — Kemudahan penggunaan:** alur katalog, checkout, dashboard, dan chatbot mudah dipahami pengguna.

**NFR-06 — Keterlacakan:** perubahan data transaksi dan status penting dapat ditelusuri melalui mekanisme yang sesuai.

**NFR-07 — Konfigurasi:** rahasia aplikasi dan kredensial pembayaran tidak disimpan dalam repository.

# 9. Teknologi yang Digunakan

- Laravel 12
- PHP
- MySQL
- Laravel Query Builder melalui DB facade
- Laravel Migrations dan Seeders
- Laravel Breeze untuk autentikasi jika kompatibel dengan kebutuhan proyek
- Laravel Socialite untuk Google OAuth
- Midtrans Snap untuk pembayaran
- Blade untuk antarmuka
- Bootstrap atau Tailwind CSS, mengikuti keputusan desain proyek
- XAMPP untuk lingkungan pengembangan lokal

# 10. Tahapan Implementasi

1. Finalisasi kebutuhan dan ERD.
2. Persiapan Laravel, database, dan Git.
3. Implementasi autentikasi dan hak akses.
4. Implementasi katalog dan manajemen produk.
5. Implementasi keranjang, checkout, dan pesanan.
6. Implementasi Midtrans dan validasi notifikasi.
7. Implementasi dashboard operasional, pengiriman, dan laporan.
8. Implementasi chatbot template dan pengaturan website.
9. Pengujian menyeluruh, dokumentasi, dan persiapan demo.

Setiap tahap harus diuji sebelum melanjutkan ke tahap berikutnya.

# 11. Kriteria Keberhasilan Produk

Website dinyatakan memenuhi kebutuhan utama apabila:
- Pelanggan dapat melihat produk baru dan bekas.
- Pelanggan dapat menyelesaikan proses pemesanan dan pembayaran sesuai alur yang diterapkan.
- Status transaksi hanya berubah berdasarkan sumber yang terverifikasi.
- Admin dan Staff hanya dapat mengakses fungsi sesuai role.
- Super Admin dapat mengelola konten website melalui dashboard.
- Chatbot dapat menjawab pertanyaan template secara otomatis dan mengambil data produk yang aktual.
- Data stok, pesanan, pembayaran, dan riwayat transaksi konsisten.
- Fitur utama telah diuji dan dokumentasi implementasi tersedia.

# 12. Instruksi untuk AI Agent

Gunakan PRD ini sebagai sumber kebutuhan produk. Sebelum mengimplementasikan fitur, periksa dokumen, struktur proyek, dan keputusan teknis yang sudah disepakati.

Jangan membuat seluruh website sekaligus. Implementasikan secara bertahap, jelaskan perubahan file, jalankan pengujian relevan, dan laporkan hasil yang benar-benar terverifikasi.

Jika ada kebutuhan yang ambigu, ajukan pertanyaan sebelum membuat asumsi yang berdampak besar. Jangan menambahkan fitur di luar ruang lingkup tanpa alasan dan persetujuan.

Mulai dengan memvalidasi rancangan sistem dan database sebelum implementasi.

