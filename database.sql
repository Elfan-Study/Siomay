-- Database Schema untuk Siomay Game Simulator
-- Drop existing database and recreate
DROP DATABASE IF EXISTS siomay_game;
CREATE DATABASE siomay_game;
USE siomay_game;

-- Tabel Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Game State
CREATE TABLE game_state (
    user_id INT PRIMARY KEY,
    cash DECIMAL(15, 2) DEFAULT 500000,
    stock INT DEFAULT 100,
    debt DECIMAL(15, 2) DEFAULT 0,
    reputation INT DEFAULT 50,
    current_day INT DEFAULT 1,
    personal_withdrawal DECIMAL(15, 2) DEFAULT 0,
    total_revenue DECIMAL(15, 2) DEFAULT 0,
    total_cost DECIMAL(15, 2) DEFAULT 0,
    is_game_over BOOLEAN DEFAULT FALSE,
    game_over_reason VARCHAR(100) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Event Templates (30+ events)
CREATE TABLE event_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    event_description TEXT NOT NULL,
    event_type ENUM('positive', 'negative') NOT NULL,
    probability_weight INT DEFAULT 10,
    choice_a_text VARCHAR(255) NOT NULL,
    choice_a_cash DECIMAL(15, 2) DEFAULT 0,
    choice_a_stock INT DEFAULT 0,
    choice_a_reputation INT DEFAULT 0,
    choice_a_debt DECIMAL(15, 2) DEFAULT 0,
    choice_a_result TEXT,
    choice_b_text VARCHAR(255) NOT NULL,
    choice_b_cash DECIMAL(15, 2) DEFAULT 0,
    choice_b_stock INT DEFAULT 0,
    choice_b_reputation INT DEFAULT 0,
    choice_b_debt DECIMAL(15, 2) DEFAULT 0,
    choice_b_result TEXT
);

-- Tabel Daily Events (log event yang terjadi)
CREATE TABLE daily_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    day INT NOT NULL,
    event_id INT NOT NULL,
    event_name VARCHAR(100),
    choice_made CHAR(1),
    impact_cash DECIMAL(15, 2) DEFAULT 0,
    impact_stock INT DEFAULT 0,
    impact_reputation INT DEFAULT 0,
    impact_debt DECIMAL(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES event_templates(id)
);

-- Tabel Daily Summary (untuk grafik)
CREATE TABLE daily_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    day INT NOT NULL,
    starting_cash DECIMAL(15, 2),
    ending_cash DECIMAL(15, 2),
    revenue DECIMAL(15, 2) DEFAULT 0,
    cost DECIMAL(15, 2) DEFAULT 0,
    profit DECIMAL(15, 2) DEFAULT 0,
    stock_sold INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- SEED DATA: EVENT TEMPLATES (30+ Events)
-- ============================================

-- POSITIVE EVENTS (60% probability)
INSERT INTO event_templates (event_name, event_description, event_type, probability_weight, 
    choice_a_text, choice_a_cash, choice_a_stock, choice_a_reputation, choice_a_debt, choice_a_result,
    choice_b_text, choice_b_cash, choice_b_stock, choice_b_reputation, choice_b_debt, choice_b_result) VALUES

('Cuaca Cerah', 'Hari ini cuaca cerah dan pembeli ramai! Kamu bisa untung besar kalau stok cukup.', 'positive', 15,
    'Beli stok ekstra 50 porsi', -300000, 50, 0, 0, 'Kamu beli stok ekstra. Modal keluar banyak tapi siap untung besar!',
    'Jual dengan stok sekarang', 0, 0, 0, 0, 'Kamu jual dengan stok yang ada. Aman tapi kehilangan peluang.'),

('Pelanggan Setia', 'Pelanggan lama mau pesan siomay rutin setiap hari (30 porsi). Tapi harus komitmen.', 'positive', 12,
    'Terima pesanan rutin', 0, 0, 10, 0, 'Reputasi naik! Tapi kamu harus jaga stok setiap hari.',
    'Tolak pesanan', 0, 0, -3, 0, 'Pelanggan kecewa. Reputasi turun sedikit.'),

('Diskon Supplier', 'Supplier kasih diskon 20% untuk pembelian hari ini!', 'positive', 10,
    'Beli banyak (100 porsi)', -400000, 100, 0, 0, 'Hemat 100rb! Tapi modal keluar banyak.',
    'Beli normal (50 porsi)', -300000, 50, 0, 0, 'Beli seperti biasa, aman.'),

('Event Sekolah', 'Ada acara sekolah, butuh katering 80 porsi siomay. Bayar di muka!', 'positive', 10,
    'Terima pesanan', 960000, -80, 5, 0, 'Untung besar! Tapi stok habis banyak.',
    'Tolak pesanan', 0, 0, 0, 0, 'Kehilangan peluang untung besar.'),

('Viral di Medsos', 'Warungmu viral di TikTok! Banyak yang mau datang. Perlu tambah kapasitas?', 'positive', 8,
    'Tambah meja & gerobak', -500000, 0, 15, 0, 'Investasi! Reputasi naik, siap terima lebih banyak pelanggan.',
    'Tetap seperti biasa', 0, 0, 5, 0, 'Reputasi naik sedikit, tapi kapasitas terbatas.'),

('Pelanggan Dermawan', 'Pelanggan kaya kasih tip besar karena puas!', 'positive', 12,
    'Terima tip', 150000, 0, 3, 0, 'Rezeki nomplok! Kas bertambah.',
    'Tolak dengan sopan', 0, 0, 5, 0, 'Reputasi naik karena rendah hati.'),

('Dapat Hadiah Lomba', 'Warungmu menang lomba UMKM! Hadiah uang tunai.', 'positive', 5,
    'Investasi ke usaha', 300000, 30, 5, 0, 'Modal bertambah dan stok bertambah!',
    'Simpan untuk darurat', 300000, 0, 0, 0, 'Kas bertambah, aman untuk cadangan.'),

('Kerjasama Kantor', 'Kantor dekat warung mau pesan siomay tiap hari (20 porsi).', 'positive', 10,
    'Terima kerjasama', 0, 0, 8, 0, 'Reputasi naik! Pendapatan stabil.',
    'Tolak', 0, 0, 0, 0, 'Tidak ada perubahan.'),

('Supplier Baru', 'Ada supplier baru dengan harga lebih murah 15%.', 'positive', 8,
    'Ganti supplier', 0, 0, 0, 0, 'Hemat biaya produksi ke depannya!',
    'Tetap supplier lama', 0, 0, 3, 0, 'Setia ke supplier lama, reputasi naik.'),

('Hari Gajian', 'Hari gajian! Pembeli banyak yang beli siomay.', 'positive', 15,
    'Tambah stok', -200000, 40, 0, 0, 'Siap untung besar!',
    'Stok biasa', 0, 0, 0, 0, 'Jual seperti biasa.'),

('Turis Asing', 'Turis asing suka siomaymu dan mau beli banyak!', 'positive', 7,
    'Jual dengan harga premium', 200000, -20, 0, 0, 'Untung besar dari turis!',
    'Harga normal', 120000, -20, 5, 0, 'Reputasi baik, harga jujur.'),

('Resep Baru', 'Kamu dapat resep sambal baru yang enak. Pelanggan suka!', 'positive', 10,
    'Pakai resep baru', -50000, 0, 10, 0, 'Investasi bumbu, reputasi naik!',
    'Tetap resep lama', 0, 0, 0, 0, 'Aman, tidak ada perubahan.'),

('Media Lokal', 'Wartawan lokal mau liput warungmu!', 'positive', 6,
    'Terima liputan', 0, 0, 15, 0, 'Reputasi naik drastis! Warung makin terkenal.',
    'Tolak liputan', 0, 0, 0, 0, 'Tidak ada perubahan.'),

('Bantuan Pemerintah', 'Dapat bantuan UMKM dari pemerintah!', 'positive', 5,
    'Terima bantuan', 500000, 0, 0, 0, 'Modal usaha bertambah!',
    'Tolak bantuan', 0, 0, 3, 0, 'Mandiri, reputasi naik sedikit.'),

('Pelanggan Ulang Tahun', 'Pelanggan rayain ulang tahun, pesan 50 porsi!', 'positive', 8,
    'Kasih diskon 10%', 540000, -50, 8, 0, 'Untung lumayan, reputasi naik!',
    'Harga normal', 600000, -50, 0, 0, 'Untung maksimal.'),

-- NEGATIVE EVENTS (40% probability)
('Harga Ikan Naik', 'Harga ikan naik 25%! Biaya produksi membengkak.', 'negative', 12,
    'Naikkan harga jual', 0, 0, -8, 0, 'Untung tetap tapi pelanggan komplain. Reputasi turun.',
    'Tetap harga lama', 0, 0, 3, 0, 'Rugi per porsi, tapi pelanggan senang. Reputasi naik.'),

('Gas Habis', 'Gas tiba-tiba habis! Harus beli sekarang atau tutup hari ini.', 'negative', 10,
    'Beli gas baru', -120000, 0, 0, 0, 'Kas keluar tapi bisa jualan.',
    'Tutup hari ini', 0, 0, -5, 0, 'Kehilangan pendapatan, reputasi turun.'),

('Stok Basi', 'Stok terlalu banyak, 30 porsi basi!', 'negative', 10,
    'Jual murah setengah harga', 90000, -30, -3, 0, 'Dapat kas sedikit, reputasi turun.',
    'Buang semua', 0, -30, 0, 0, 'Rugi total, stok hilang.'),

('Tetangga Pinjam', 'Tetangga minta pinjam uang 200rb untuk keperluan mendesak.', 'negative', 12,
    'Pinjamkan dari kas usaha', -200000, 0, 5, 0, 'Kas berkurang, tapi hubungan baik.',
    'Tolak dengan sopan', 0, 0, -3, 0, 'Kas aman, tapi hubungan agak renggang.'),

('Anak Sakit', 'Anak sakit, perlu uang 300rb untuk berobat.', 'negative', 10,
    'Ambil dari kas usaha', -300000, 0, 0, 0, 'Kas usaha berkurang untuk keperluan pribadi.',
    'Pinjam ke bank', 300000, 0, 0, 300000, 'Dapat uang tapi utang bertambah.'),

('Kompetitor Baru', 'Warung siomay baru buka di sebelah, harga lebih murah!', 'negative', 12,
    'Turunkan harga (perang harga)', 0, 0, -5, 0, 'Margin tipis, reputasi turun.',
    'Tingkatkan kualitas', -200000, 0, 10, 0, 'Investasi kualitas, reputasi naik!'),

('Hujan Deras', 'Hujan deras seharian, pembeli sepi.', 'negative', 15,
    'Tetap buka', -50000, 0, 0, 0, 'Rugi operasional, pembeli sedikit.',
    'Tutup warung', 0, 0, 0, 0, 'Tidak ada pendapatan hari ini.'),

('Listrik Mati', 'Listrik mati, tidak bisa masak!', 'negative', 8,
    'Sewa genset', -150000, 0, 0, 0, 'Bisa jualan tapi biaya tinggi.',
    'Tutup hari ini', 0, 0, -5, 0, 'Kehilangan pendapatan.'),

('Peralatan Rusak', 'Panci kukusan rusak! Perlu beli baru.', 'negative', 8,
    'Beli panci baru', -250000, 0, 0, 0, 'Investasi peralatan.',
    'Pinjam panci tetangga', 0, 0, 3, 0, 'Hemat uang, hubungan baik.'),

('Bahan Baku Telat', 'Supplier telat kirim, stok menipis!', 'negative', 10,
    'Beli dari pasar (lebih mahal)', -350000, 40, 0, 0, 'Bisa jualan tapi biaya tinggi.',
    'Tutup sementara', 0, 0, -8, 0, 'Pelanggan kecewa.'),

('Utang Jatuh Tempo', 'Utang ke supplier jatuh tempo, harus bayar sekarang!', 'negative', 8,
    'Bayar dari kas', -400000, 0, 0, -400000, 'Utang lunas, kas berkurang.',
    'Minta perpanjang waktu', 0, 0, -5, 50000, 'Reputasi turun, utang bertambah (denda).'),

('Keluarga Minta Uang', 'Keluarga minta uang 250rb untuk keperluan mendadak.', 'negative', 12,
    'Kasih dari kas usaha', -250000, 0, 0, 0, 'Kas berkurang untuk pribadi.',
    'Tolak', 0, 0, -5, 0, 'Hubungan keluarga renggang.'),

('Komplain Pelanggan', 'Pelanggan komplain rasa kurang enak.', 'negative', 10,
    'Ganti gratis', -60000, -10, 5, 0, 'Rugi tapi reputasi pulih.',
    'Abaikan', 0, 0, -10, 0, 'Reputasi turun drastis.'),

('Pajak Mendadak', 'Petugas pajak datang, harus bayar retribusi.', 'negative', 7,
    'Bayar pajak', -180000, 0, 0, 0, 'Kas berkurang tapi legal.',
    'Nego/tunda', 0, 0, -3, 100000, 'Reputasi turun, utang bertambah.'),

('Gerobak Rusak', 'Roda gerobak rusak, perlu diperbaiki.', 'negative', 8,
    'Servis gerobak', -100000, 0, 0, 0, 'Kas keluar untuk perbaikan.',
    'Pakai apa adanya', 0, 0, -5, 0, 'Reputasi turun, terlihat kurang rapi.');
