===========================================
DOKUMENTASI FILE - SIMULATOR WARUNG SIOMAY
===========================================

DESKRIPSI PROYEK:
Game edukasi literasi keuangan UMKM berbasis web dengan tema penjualan siomay.
Pemain belajar mengelola keuangan usaha melalui simulasi harian dengan event acak.

===========================================
FILE UTAMA
===========================================

1. config.php
   - Konfigurasi koneksi database
   - Mengatur PDO untuk MySQL
   - Database: siomay_game
   - Digunakan oleh semua file lain

2. database.sql
   - Schema database lengkap
   - Tabel: users, game_state, event_templates, daily_events, daily_summary
   - Berisi 30+ event templates (positif & negatif)
   - Modal awal: Rp 500.000, Stok awal: 100 porsi

===========================================
AUTENTIKASI
===========================================

3. index.php
   - Halaman login
   - Form username & password
   - Redirect ke dashboard setelah login berhasil

4. register.php
   - Halaman pendaftaran user baru
   - Membuat akun dan inisialisasi game state
   - Modal awal: Rp 500rb, Stok: 100 porsi, Reputasi: 50

5. logout.php
   - Menghapus session
   - Redirect ke halaman login

===========================================
GAMEPLAY INTI
===========================================

6. dashboard.php
   - Halaman utama setelah login
   - Menampilkan status: Kas, Stok, Utang, Reputasi, Hari ke-X
   - Grafik profit dan kas (Chart.js)
   - Analisis performa (setelah 3+ hari)
   - Tombol: Mulai Hari Baru, Beli Stok, Restart
   - Bisa diakses meskipun game over (untuk lihat statistik)

7. start_day.php
   - Memulai hari baru
   - OTOMATIS beli 100 porsi stok (Rp 300.000)
   - Generate 2-3 event acak untuk hari ini
   - Cek apakah uang cukup untuk beli stok (jika tidak = game over)
   - Redirect ke event pertama

8. event.php
   - Menampilkan event dan pilihan keputusan
   - 2 pilihan (A atau B) dengan dampak berbeda
   - Menampilkan status saat ini (kas, stok, reputasi)
   - Menampilkan preview dampak setiap pilihan

9. process_choice.php
   - Memproses pilihan pemain
   - Menerapkan dampak ke game state (kas, stok, reputasi, utang)
   - Mencatat event ke database
   - Redirect ke result.php

10. result.php
    - Menampilkan hasil dari keputusan
    - Menampilkan status terbaru
    - Tombol lanjut ke event berikutnya atau tutup buku

11. end_day.php
    - Tutup buku harian
    - Hitung penjualan otomatis:
      * Reputasi = chance pelanggan datang (max 100 orang)
      * Setiap pembeli beli 3-5 porsi (80%) atau 1 porsi (20%)
      * Max 500 porsi terjual per hari
    - Hitung laba/rugi
    - Update reputasi (turun jika banyak pembeli cuma beli 1)
    - Cek game over (kas habis, utang >5jt, reputasi <10)
    - Cek milestone (hari 7, 15, 30)
    - Simpan daily summary

===========================================
FITUR TAMBAHAN
===========================================

12. restock.php
    - Beli stok manual (100 porsi = Rp 300.000)
    - Cek apakah uang cukup
    - Cek apakah inventori tidak melebihi 500 porsi
    - Redirect ke dashboard dengan pesan sukses/error

13. milestone.php
    - Laporan evaluasi di hari 7, 15, dan 30
    - Analisis performa: rating, total profit, tren grafik
    - Saran perbaikan berdasarkan kondisi game
    - Hari 7 & 15: Bisa lanjut atau restart
    - Hari 30: Wajib restart (siklus selesai)

14. gameover.php
    - Layar game over dengan analisis edukatif
    - Penjelasan kenapa kalah (bangkrut/utang/reputasi/no stock money)
    - Pelajaran yang bisa diambil
    - Statistik: hari bertahan, total profit, uang pribadi, utang
    - Saran untuk permainan berikutnya
    - Tombol kembali ke dashboard

15. restart.php
    - Reset game state ke awal
    - Kas: Rp 500rb, Stok: 100, Reputasi: 50, Hari: 1
    - Hapus history (daily_events, daily_summary)
    - Redirect ke dashboard

===========================================
FILE LAMA (TIDAK DIGUNAKAN)
===========================================

16. update_db.php
    - Script untuk update database schema lama
    - Menambahkan kolom debt dan shop_level
    - Bisa dihapus setelah database diupdate

17. action.php
    - File lama untuk handle action (buy, sell, dll)
    - Sudah tidak dipakai di versi baru
    - Digantikan oleh start_day.php, event.php, dll

===========================================
MEKANISME GAME
===========================================

SIKLUS HARIAN:
1. Mulai Hari → Auto beli 100 porsi (Rp 300rb)
2. Hadapi 2-3 Event → Pilih keputusan A atau B
3. Tutup Buku → Penjualan otomatis berdasarkan reputasi
4. Lihat Laba/Rugi → Lanjut ke hari berikutnya

SISTEM REPUTASI:
- Reputasi = % chance pelanggan datang
- Max 100 pelanggan potensial per hari
- Reputasi 50 = ~50 pelanggan datang
- Reputasi 100 = ~100 pelanggan datang

SISTEM PENJUALAN:
- Harga jual: Rp 15.000/porsi
- Harga beli: Rp 3.000/porsi
- Profit: Rp 12.000/porsi
- Setiap pembeli beli 3-5 porsi (normal) atau 1 porsi (tidak puas)

GAME OVER:
- Kas <= 0 (bangkrut)
- Utang > Rp 5.000.000 (debt trap)
- Reputasi < 10 (pelanggan kabur)
- Tidak cukup uang beli stok (Rp 300rb)

MILESTONE:
- Hari 7: Evaluasi minggu pertama (bisa lanjut/restart)
- Hari 15: Evaluasi pertengahan (bisa lanjut/restart)
- Hari 30: Evaluasi akhir (wajib restart)

===========================================
TEKNOLOGI
===========================================

Backend: PHP + MySQL (PDO)
Frontend: HTML, CSS, Bootstrap 5
Chart: Chart.js
Icons: Font Awesome 6
Database: MySQL (siomay_game)

===========================================
CARA MENJALANKAN
===========================================

1. Install XAMPP
2. Jalankan Apache dan MySQL
3. Import database.sql ke phpMyAdmin
4. Akses: http://localhost/siomay_game/
5. Daftar akun baru
6. Mulai bermain!

===========================================
FITUR EDUKASI
===========================================

✓ Cash Flow Management
✓ Pemisahan Uang Pribadi & Usaha
✓ Manajemen Stok & Inventori
✓ Manajemen Utang
✓ Reputasi Bisnis
✓ Pengambilan Keputusan
✓ Analisis Profit/Loss
✓ Modal Kerja

===========================================
