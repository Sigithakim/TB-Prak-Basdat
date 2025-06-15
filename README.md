# 🚚 Jasa Kirim Barang - Sistem Pengiriman Berbasis PHP & MySQL

Aplikasi **Jasa Kirim Barang** adalah sistem informasi pengiriman barang yang dibangun menggunakan **PHP** dan **MySQL**. Project ini mendukung proses pengelolaan data pelanggan, gudang, kurir, barang, order/pengiriman, tracking perjalanan barang, dan penyimpanan barang secara terintegrasi serta real-time.

---

## ✨ Fitur Utama

* **Autentikasi User (Login/Logout)**
* **Manajemen Customer**: Tambah, edit, hapus, dan lihat data pelanggan.
* **Manajemen Gudang**: CRUD data gudang tujuan/transit.
* **Manajemen Kurir**: CRUD data kurir pengantar barang.
* **Manajemen Barang**: CRUD data barang yang dikirim.
* **Manajemen Order/Pengiriman**: CRUD pengiriman, relasi ke customer, kurir, gudang, dan barang.
* **Tracking Pengiriman (Trek)**: Catat & tampilkan riwayat perjalanan barang secara detail (lokasi, waktu, status).
* **Manajemen Penyimpanan Barang**: Catat barang yang transit di gudang.
* **Update Status Otomatis**: Status pengiriman otomatis terupdate ketika ada perubahan trek (tracking), berkat trigger di database.
* **Laporan & Data Gabungan**: Dengan stored procedure dan view, aplikasi siap digunakan untuk pelaporan dan monitoring.

---

## 🛠️ Teknologi

* **Backend**: PHP (tanpa framework)
* **Database**: MySQL/MariaDB (`jasakirim`)
* **UI**: HTML + CSS sederhana (bisa dikembangkan ke Bootstrap dsb.)
* **Session**: Untuk autentikasi user (admin/operator)

---

## 📁 Struktur Folder & File

```
.
├── barang.php           # CRUD data barang
├── cek.php              # Proteksi halaman (cek login)
├── customer.php         # CRUD data customer/pelanggan
├── function.php         # Koneksi DB & utility function
├── gudang.php           # CRUD data gudang
├── index.php            # Dashboard utama
├── kurir.php            # CRUD data kurir
├── login.php            # Login user
├── logout.php           # Logout user
├── order.php            # CRUD data order/pengiriman
├── penyimpanan.php      # CRUD data penyimpanan barang
├── trek.php             # CRUD tracking pengiriman
├── jasakirim.sql        # File SQL database (struktur & sample data)
└── README.md            # Dokumentasi ini
```

---

## ⚙️ Instalasi & Setup

1. **Clone Repository**

   ```bash
   git clone https://github.com/username/jasa-kirim-barang.git
   cd jasa-kirim-barang
   ```

2. **Setup Database**

   * Import file `jasakirim.sql` ke MySQL via phpMyAdmin atau terminal:

     ```bash
     mysql -u root -p < jasakirim.sql
     ```
   * Pastikan user & password di file `function.php` sudah sesuai dengan konfigurasi server lokal kamu.

3. **Jalankan Aplikasi**

   * Simpan semua file ke folder web server lokal kamu (`htdocs` jika pakai XAMPP/Laragon).
   * Akses aplikasi via browser:

     ```
     http://localhost/jasa-kirim-barang/login.php
     ```

4. **Login**

   * Gunakan data user dari tabel `login` pada database (`email` & `password` default di file SQL).

---

## 🚦 Alur Kerja & Flow Aplikasi

1. **Login** sebagai admin/operator.
2. **Kelola master data**: customer, gudang, kurir, barang.
3. **Input order/pengiriman**: isi data pengirim, penerima, barang, kurir, gudang.
4. **Input trek/tracking**: update posisi barang, status perjalanan, lokasi, dan waktu.
5. **Manajemen penyimpanan**: catat bila barang transit di gudang tertentu.
6. **Logout** saat selesai.

Setiap proses **CRUD** (Create, Read, Update, Delete) dilakukan melalui tampilan form & tabel pada aplikasi.

---

## 🔗 Relasi & Struktur Database

* Satu **order** terhubung ke **customer**, **kurir**, **gudang** dan bisa memiliki beberapa **barang**.
* **Tracking (trek)** menyimpan riwayat posisi & status barang dalam perjalanan.
* **Penyimpanan** mencatat kapan dan di gudang mana barang disimpan sementara.
* **Trigger** otomatis update status pengiriman pada order jika ada update pada trek.
* **Stored procedure** dan **view** siap pakai untuk pelaporan dan pengambilan data gabungan.

Diagram sederhana relasi:

```
customer ---- order ---- barang
                   \         /
                kurir    trek
                   |         \
                gudang --- penyimpanan_barang
```

---

## 📝 Catatan Penting

* **File `function.php`** adalah kunci: seluruh koneksi & query ke database ada di sini.
* **Setiap file PHP** punya pola yang mirip: proteksi login (`cek.php`), form input, tabel data, dan fungsi CRUD.
* **Sistem autentikasi** berbasis session PHP (bukan token/JWT).
* **Trigger & procedure** di database membuat update status & laporan lebih efisien.
* **Tidak menggunakan framework**, sehingga mudah dipelajari untuk pemula.

---

## 📚 Kontributor

* Sigit Firman Hakim
* Alfian Arsyad Wijaya
* Hanif Lukman
* Asep Ramdani

---

## ☕ Lisensi

Project ini hanya untuk kebutuhan pembelajaran. Silakan gunakan dan kembangkan dengan menyertakan kredit kepada penulis dan pengembang awal.

---
