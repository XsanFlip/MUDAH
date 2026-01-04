# 🛡️ MUDAH - Monitoring Unified Detection for Application Holes

Sebuah skrip PHP tunggal untuk memvisualisasikan laporan hasil pemindaian kerentanan (vulnerability scan) dari tools DursGo https://github.com/roomkangali/dursgo kedalam sebuah dasbor yang interaktif dan mudah dibaca. Dasbor ini dilindungi oleh halaman login dengan CAPTCHA.

## Deskripsi

Alat ini dirancang untuk para pentester atau analis keamanan yang ingin cara cepat dan efisien untuk menyajikan temuan kerentanan dari file laporan dursgo yang berformat JSON. Alih-alih membaca file JSON yang mentah, skrip ini mengubahnya menjadi dasbor visual dengan ringkasan, grafik, dan tabel yang dapat difilter.

Semua fungsionalitas, mulai dari otentikasi, logika backend, hingga tampilan frontend, dibungkus dalam satu file `index.php` untuk portabilitas maksimal.

## Fitur

- **Halaman Login Aman**: Otentikasi dengan username/password (hash bcrypt) dan validasi CAPTCHA.
- **Dasbor Dinamis**: Secara otomatis memuat laporan JSON terbaru dan menyediakan dropdown untuk beralih antar laporan.
- **Visualisasi Data**: Ringkasan metrik kunci, diagram lingkaran untuk distribusi severity, dan diagram batang untuk top 5 tipe kerentanan.
- **Tabel Interaktif**: Daftar kerentanan yang dapat dicari (search) dan disaring (filter) berdasarkan tingkat keparahan.
- **Detail Modal**: Klik pada baris tabel untuk melihat detail lengkap kerentanan, termasuk payload dan saran remediasi.
- **Portabel**: Seluruh aplikasi hanya terdiri dari satu file.
- **Kustomisasi Mudah**: Teks panduan dan judul dapat disesuaikan untuk kebutuhan laporan pentest.
  
<img width="1920" height="2087" alt="Screenshot 2026-01-04 at 19-20-35 MUDAH - Monitoring Unified Detection for Application Holes" src="https://github.com/user-attachments/assets/6547bf37-a8bf-4e9f-bc3b-511629103fc7" />

## Prasyarat

Sebelum menjalankan skrip ini, pastikan server Anda memenuhi persyaratan berikut:

1.  **Web Server**: Apache, Nginx, atau sejenisnya.
2.  **PHP**: Versi 7.4 atau lebih baru direkomendasikan.
3.  **Ekstensi PHP `gd`**: Diperlukan untuk menghasilkan gambar CAPTCHA.

## Instalasi & Konfigurasi

**Langkah 1: Instalasi Ekstensi GD**

Pastikan ekstensi `php-gd` terinstal. Jika belum, gunakan perintah yang sesuai untuk sistem operasi Anda.

-   Untuk Debian/Ubuntu:
    ```bash
    sudo apt-get update
    sudo apt-get install php-gd
    ```
-   Untuk CentOS/RHEL:
    ```bash
    sudo dnf install php-gd
    ```
Jangan lupa untuk me-restart web server Anda setelah instalasi (misal: `sudo systemctl restart apache2`).

**Langkah 2: Tempatkan File**

Salin file `index.php` ke direktori root web server Anda (misalnya, `/var/www/html/`).

**Langkah 3: Tempatkan Laporan JSON**

Letakkan semua file laporan pemindaian kerentanan Anda (yang berformat `.json`) di direktori yang sama dengan file `index.php`.

**Langkah 4: (Opsional) Ubah Kredensial**

Kredensial login didefinisikan di bagian atas `index.php`.
```php
// Konfigurasi Login
define('LOGIN_USERNAME', 'pentester');
define('LOGIN_PASSWORD_HASH', '$2a$12$Z35KQh11M9KfVPXGXMird.HUEW29qtelS7fwcQkzrk/2MT3Y.rP4e'); durg0mantab
```
Untuk mengubah password, Anda perlu membuat hash bcrypt baru untuk password yang Anda inginkan dan mengganti nilai `LOGIN_PASSWORD_HASH`.

## Penggunaan

1.  Buka browser dan arahkan ke lokasi `index.php` (misal: `http://localhost/index.php`).
2.  Anda akan disambut oleh halaman login.
3.  Masukkan kredensial default dan isi CAPTCHA, lalu klik "Log In".
4.  Dasbor akan secara otomatis menampilkan data dari **laporan JSON terbaru**.
5.  Gunakan menu dropdown "Pilih Laporan" untuk beralih dan menganalisis laporan lainnya.

### Kredensial Default

-   **Username**: `pentester`
-   **Password**: `durg0mantab`

## Lisensi

Proyek ini dilisensikan di bawah **MIT License**. Lihat teks lengkap di bawah ini.

```
MIT License

Copyright (c) 2026 Xsan-Lahci

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## Shout Out

* Kang Ali -* https://github.com/roomkangali/dursgo
