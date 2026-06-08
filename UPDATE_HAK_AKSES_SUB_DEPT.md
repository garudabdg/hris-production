# Dokumentasi Update: Hak Akses Sub-Departemen & Tracking Presensi

Berikut adalah rangkuman dari seluruh pekerjaan yang telah dilakukan untuk menambahkan fitur hak akses **Sub-Departemen** bagi level admin/pengguna, serta beberapa perbaikan pada modul lainnya:

## 1. Perubahan Database (Migration)
- Membuat file migrasi baru untuk menambahkan kolom `sub_departemen` ke dalam tabel pivot `user_departemen_access`.
- Kolom ini bertipe `text` dan digunakan untuk menyimpan data sub-departemen dalam format `JSON` yang dipilih saat pembuatan atau perubahan user.

## 2. Pembaruan Model (`User.php`)
- **Penambahan Pivot:** Memperbarui relasi `departemens()` agar mengambil nilai pivot `withPivot('sub_departemen')`.
- **Fungsi Baru `getDepartemenAccessMap()`:** Menambahkan sebuah fungsi _helper_ yang membaca konfigurasi pivot tersebut dan menghasilkan pemetaan hak akses departemen dan sub-departemen. Jika pengguna tidak dibatasi sub-departemen, maka hasilnya adalah akses penuh. Sebaliknya, jika dibatasi, fungsi ini akan menghasilkan daftar sub-departemen apa saja yang boleh dilihat.

## 3. Pembaruan Controller (`UserController.php`)
- Memperbarui fungsi `store` (simpan baru) dan `update` (perbarui data).
- Sistem kini akan mengambil _array_ input dari `sub_departemen_{kode_dept}` dan menggabungkannya saat sinkronisasi (`sync`) relasi _many-to-many_ ke tabel `user_departemen_access` dalam bentuk JSON.

## 4. Pembaruan Antarmuka Pengguna (UI)
- **File:** `resources/views/settings/users/create.blade.php` dan `edit.blade.php`.
- **Logika Penampilan Data:** Jika sebuah departemen memiliki data "sub_departemen" di database master `Departemen` (berupa array JSON), maka sistem akan menampilkan daftar _checkbox_ sub-departemen di bawahnya.
- **Fallback / Data Pancingan:** Data pancingan `(TEST)` yang sebelumnya disisipkan untuk membantu proses pembuatan antarmuka (UI) kini telah dihapus secara permanen sehingga tampilan _checkbox_ sepenuhnya berasal dari _database_.

## 5. Implementasi Filter Keamanan & Monitoring Data
Agar fungsi sub-departemen ini bukan hanya sekadar pajangan, kita telah mengunci akses data ke beberapa halaman sentral. Semuanya kini menggunakan `getDepartemenAccessMap()` untuk pembatasan data yang sangat spesifik (berbasis *query builder*):

- **Master Karyawan (`KaryawanController@index`):** Admin yang dibatasi hanya akan melihat karyawan yang ada di sub-departemen mereka.
- **Tabel Monitoring Absen (`PresensiController@index`):** Hanya akan menampilkan histori presensi/absen karyawan sesuai sub-departemen.
- **Peta/Tracking Absen (`TrackingPresensiController@getPresensiData`):** Lokasi pada _map_ hanya akan muncul untuk karyawan yang sub-departemennya dikelola oleh sang Admin.

## 6. Pembaruan Detail Karyawan & Unduhan PDF
Sesuai dengan _request_ Anda, fitur di halaman **Detail Karyawan** (`datamaster.karyawan.show`) dan hasil cetak PDF-nya juga telah diselesaikan:
- **Integrasi Aset & IT Support:** Detail karyawan kini memuat informasi **Total Tiket IT yang dibuat** serta daftar **Kode Aset** yang dipercayakan kepada karyawan tersebut.
- **Desain PDF Profesional:** Layout dokumen PDF (_Unduh PDF_) telah di-desain dengan sangat rapi dan profesional.
- **Kop Surat Dinamis:** Menambahkan komponen kop surat berisikan **Logo Perusahaan**, **Nama Cabang**, **Alamat Cabang**, dan **Nomor Telepon** pada bagian _header_ PDF.
- **Nomor Kontrak:** Menambahkan detail **Nomor Kontrak** pada halaman pertama profil karyawan.
- **Pemisah Halaman (Page Break):** Untuk menjaga kerapian, bagian informasi **Aset & Dukungan IT** beserta Riwayat Mutasi dan Sertifikasi kini dipisahkan dan dicetak mulai dari **Halaman 2**.

## 7. Perbaikan UI Tracking Presensi (Javascript & Modal Foto)
- **Resolusi Crash Map:** Mengatasi masalah struktur data (Collections vs Array JSON) yang dikirim dari Backend sehingga data peta dan tabel sukses dirender tanpa memicu teks macet "Memuat data...".
- **Penyesuaian Tanggal Javascript:** Memperbaiki sistem penguraian format jam masuk/keluar dari database (`TIME` literal) menjadi teks yang aman digunakan di _browser_ manapun, menghindari _error_ `Invalid Date`.
- **Modifikasi Interaktif Tabel & Foto:** Menyingkirkan *event listeners* bermasalah dari tag HTML (`onclick="..."`) dan menggantinya dengan sistem **jQuery Event Delegation**.
- **Fitur View Image Dinamis:** Membuang penggunaan _Bootstrap Native Modal_ yang rentan bersembunyi di balik layout halaman, lalu merombak ulang fitur "Klik Foto Absen" menggunakan **SweetAlert2 (Swal.fire)** yang jauh lebih indah, kebal dari blokir _strict mode_ browser, dan bebas _error_ z-index.
