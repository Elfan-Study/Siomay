# 🍢 Simulator Warung Siomay

**Game Edukasi Literasi Keuangan UMKM Berbasis Web**

Simulator bisnis siomay yang mengajarkan konsep keuangan melalui gameplay event-driven yang interaktif dan edukatif.

![Game Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple)
![License](https://img.shields.io/badge/license-MIT-green)

---

## 📖 Tentang Project

**Simulator Warung Siomay** adalah game edukasi berbasis web yang dirancang untuk mengajarkan literasi keuangan kepada pelaku UMKM melalui simulasi bisnis siomay. Pemain akan belajar mengelola:

- 💰 **Cash Flow Management**
- 📦 **Manajemen Stok & Inventori**
- 💳 **Manajemen Utang**
- ⭐ **Reputasi Bisnis**
- 🎯 **Pengambilan Keputusan Bisnis**
- 📊 **Analisis Profit/Loss**

---

## ✨ Fitur Utama

### 🎮 Gameplay Event-Based
- **30+ Event Acak** dengan pilihan keputusan yang berdampak
- **Sistem Reputasi** yang mempengaruhi jumlah pelanggan
- **Mekanisme Penjualan Otomatis** berdasarkan stok dan reputasi
- **Milestone Evaluasi** di hari 7, 15, dan 30

### 📊 Sistem Keuangan Realistis
- Modal awal: **Rp 500.000**
- Stok awal: **100 porsi**
- Harga jual: **Rp 15.000/porsi**
- Biaya produksi: **Rp 3.000/porsi**
- Profit margin: **Rp 12.000/porsi**

### 🎯 Feedback Edukatif
- **Analisis Performa** real-time
- **Saran Perbaikan** berdasarkan kondisi game
- **Game Over Screen** dengan penjelasan edukatif
- **Grafik Keuangan** (Chart.js)

### 🎨 UI/UX Modern
- **Cartoon Flat Design** yang menarik
- **Responsive Design** untuk semua device
- **Animasi Interaktif**
- **Maskot Karakter** yang ekspresif

---

## 🚀 Cara Install

### Prerequisites
- **XAMPP** (Apache + MySQL)
- **PHP 7.4+**
- **MySQL 5.7+**
- **Web Browser** modern

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/Elfan-Study/Siomay.git
   cd Siomay
   ```

2. **Pindahkan ke XAMPP**
   ```bash
   # Windows
   copy ke C:\xampp\htdocs\siomay_game
   
   # Linux/Mac
   copy ke /opt/lampp/htdocs/siomay_game
   ```

3. **Setup Database**
   - Buka phpMyAdmin: `http://localhost/phpmyadmin`
   - Buat database baru: `siomay_game`
   - Import file: `database.sql`

4. **Konfigurasi**
   - Edit `config.php` jika perlu (default sudah OK)
   ```php
   $host = 'localhost';
   $dbname = 'siomay_game';
   $username = 'root';
   $password = '';
   ```

5. **Jalankan**
   - Start Apache & MySQL di XAMPP
   - Buka browser: `http://localhost/siomay_game/`
   - Daftar akun baru dan mulai bermain!

---

## 🎮 Cara Bermain

### Siklus Harian

1. **Mulai Hari Baru**
   - Otomatis beli 100 porsi stok (Rp 300.000)
   - Hadapi 2-3 event acak

2. **Pilih Keputusan**
   - Setiap event punya 2 pilihan (A atau B)
   - Setiap pilihan punya dampak berbeda

3. **Tutup Buku**
   - Penjualan otomatis berdasarkan reputasi
   - Lihat laba/rugi harian
   - Lanjut ke hari berikutnya

### Sistem Reputasi

- **Reputasi = % Chance Pelanggan Datang**
- Max 100 pelanggan potensial per hari
- Reputasi 50 = ~50 pelanggan datang
- Reputasi 100 = ~100 pelanggan datang

### Game Over Conditions

- ❌ Kas <= 0 (Bangkrut)
- ❌ Utang > Rp 5.000.000 (Debt Trap)
- ❌ Reputasi < 10 (Pelanggan Kabur)
- ❌ Tidak cukup uang beli stok (< Rp 300.000)

### Milestone

- **Hari 7**: Evaluasi minggu pertama (bisa lanjut/restart)
- **Hari 15**: Evaluasi pertengahan (bisa lanjut/restart)
- **Hari 30**: Evaluasi akhir (wajib restart)

---

## 🛠️ Teknologi

- **Backend**: PHP 7.4+ dengan PDO
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5
- **Charts**: Chart.js
- **Icons**: Font Awesome 6
- **Fonts**: Google Fonts (Fredoka One, Nunito)

---

## 📁 Struktur File

```
siomay_game/
├── assets/
│   └── style.css          # Global CSS framework
├── config.php             # Database configuration
├── database.sql           # Database schema + seed data
├── index.php              # Login page
├── register.php           # Registration page
├── dashboard.php          # Main dashboard
├── start_day.php          # Start new day logic
├── event.php              # Event display
├── process_choice.php     # Process player choice
├── result.php             # Show choice result
├── end_day.php            # End of day summary
├── milestone.php          # Milestone feedback
├── gameover.php           # Game over screen
├── restock.php            # Manual restock
├── restart.php            # Restart game
├── logout.php             # Logout
└── README.md              # This file
```

---

## 🎓 Konsep Edukasi

Game ini mengajarkan:

1. **Cash Flow Management**
   - Pentingnya menjaga kas untuk operasional
   - Bahaya menghabiskan semua profit

2. **Pemisahan Uang Pribadi & Usaha**
   - Dampak mengambil uang usaha untuk pribadi
   - Pentingnya modal kerja

3. **Manajemen Stok**
   - Balance antara stok dan penjualan
   - Bahaya overstocking (modal terkunci)

4. **Manajemen Utang**
   - Utang produktif vs konsumtif
   - Debt trap dan cara menghindarinya

5. **Reputasi Bisnis**
   - Pentingnya kepuasan pelanggan
   - Reputasi = lebih banyak pembeli

6. **Pengambilan Keputusan**
   - Analisis risiko vs reward
   - Berpikir jangka panjang

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan:

1. Fork repository ini
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📝 License

Distributed under the MIT License. See `LICENSE` for more information.

---

## 👨‍💻 Author

**Elfan Study**

- GitHub: [@Elfan-Study](https://github.com/Elfan-Study)
- Repository: [Siomay](https://github.com/Elfan-Study/Siomay)

---

## 🙏 Acknowledgments

- Bootstrap Team
- Chart.js Team
- Font Awesome Team
- Google Fonts

---

## 📸 Screenshots

### Login Page
![Login](docs/screenshots/login.png)

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)

### Event Decision
![Event](docs/screenshots/event.png)

### Game Over
![Game Over](docs/screenshots/gameover.png)

---

## 🔮 Future Features

- [ ] Multiplayer mode
- [ ] Leaderboard
- [ ] More event variations
- [ ] Achievement system
- [ ] Save/Load game
- [ ] Mobile app version

---

**Made with ❤️ for UMKM Education**
