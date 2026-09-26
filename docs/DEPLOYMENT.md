# Panduan Deploy KPMI Pekalongan

Dokumen ini berisi panduan alur deploy aplikasi **KPMI Pekalongan** ke environment **Shared Hosting** (cPanel / SSH) dan **VPS Linuxid (Ubuntu / Nginx / PHP-FPM)**.

---

## 1. Persyaratan Sistem

- **PHP**: >= 8.2 (Laravel 13 requirement) dengan ekstensi `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo_mysql` / `pdo_mariadb`, `session`, `tokenizer`, `xml`.
- **Database**: MariaDB >= 10.4 atau MySQL >= 8.0.
- **Web Server**: Nginx atau Apache (dengan `mod_rewrite`).
- **Composer**: Minimal Composer v2.x.
- **Node.js & NPM**: Minimal v18+ (untuk build asset frontend Vite jika diproses di server).

---

## 2. Persiapan Sebelum Deploy

1. Salin `.env.production` menjadi `.env` pada environment target:
   ```bash
   cp .env.production .env
   ```
2. Generate Application Key:
   ```bash
   php artisan key:generate
   ```
3. Sesuaikan kredensial Database (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) dan `APP_URL`.
4. Pastikan `APP_DEBUG=false` dan `APP_ENV=production`.

---

## 3. Deployment di Shared Hosting (cPanel / SSH)

### Langkah Deploy:

1. **Upload Source Code**:
   - Upload seluruh file proyek ke folder luar `public_html` (misal `/home/username/kpmi-app`) via SSH / FTP / cPanel File Manager.
2. **Arahkan Document Root / Symlink**:
   - Pindahkan isi folder `public/` ke `public_html/` atau ubah Document Root domain di cPanel mengarah ke `/home/username/kpmi-app/public`.
   - Jika memindahkan isi `public/` ke `public_html/`, perbarui path di `public_html/index.php`:
     ```php
     require __DIR__.'/../kpmi-app/vendor/autoload.php';
     $app = require_once __DIR__.'/../kpmi-app/bootstrap/app.php';
     ```
3. **Jalankan Perintah Instalasi (via SSH)**:
   ```bash
   cd /home/username/kpmi-app
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. **Izin Folder (Permissions)**:
   Pastikan folder `storage` dan `bootstrap/cache` memiliki izin tulis (775 atau 755).

---

## 4. Deployment di VPS Linuxid (Nginx + PHP-FPM)

### 4.1 Configuration Nginx (`/etc/nginx/sites-available/kpmi`)

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name kpmi-pekalongan.or.id www.kpmi-pekalongan.or.id;
    root /var/www/kpmi-pekalongan/public;

    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site & reload Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/kpmi /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 4.2 Setup Queue Worker (Systemd Service)

Buat file `/etc/systemd/system/kpmi-worker.service`:

```ini
[Unit]
Description=KPMI Pekalongan Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/kpmi-pekalongan/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Jalankan service worker:
```bash
sudo systemctl daemon-reload
sudo systemctl enable kpmi-worker
sudo systemctl start kpmi-worker
```

### 4.3 Setup Cron Job (Schedule Run)

Tambahkan ke crontab user `www-data`:
```bash
sudo crontab -u www-data -e
```
Masukkan baris berikut:
```cron
* * * * * cd /var/www/kpmi-pekalongan && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Checklist Backup Database Berkala

1. **Automated Backup Command (Dump SQL)**:
   ```bash
   mysqldump -u kpmi_user -p'secret_password' kpmi_pekalongan | gzip > /var/backups/kpmi_db_$(date +\%Y\%m\%d_\%H\%m).sql.gz
   ```
2. **Cron Job Backup Harian (Jam 02:00 Pagi)**:
   ```cron
   0 2 * * * mysqldump -u kpmi_user -p'secret_password' kpmi_pekalongan | gzip > /var/backups/kpmi_db_$(date +\%Y\%m\%d).sql.gz
   ```
3. **Retention Policy**:
   Hapus backup yang lebih tua dari 30 hari:
   ```bash
   find /var/backups/kpmi_db_*.sql.gz -mtime +30 -exec rm {} \;
   ```
4. **Penyimpanan Offsite**:
   Gunakan S3 / Rclone untuk mengunggah berkas backup `.sql.gz` dan folder `storage/app/public` (termasuk foto/attachment) ke cloud storage eksternal secara otomatis.
