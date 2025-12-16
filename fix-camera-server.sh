#!/bin/bash
# Script untuk fix error getUserMedia di server
# Jalankan di server: bash fix-camera-server.sh

cd /var/www/html/absensi-ppkp

FILE="resources/views/admin/absensi/index.blade.php"

# Backup file
cp "$FILE" "${FILE}.backup"

# Cari baris "// Camera Functions for Check In" dan tambahkan fungsi getUserMedia setelahnya
# Gunakan sed untuk insert setelah baris yang mengandung "Camera Functions for Check In"
sed -i '/\/\/ Camera Functions for Check In/a\
    // Check and get getUserMedia with fallback\
    function getUserMedia(constraints) {\
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {\
            return navigator.mediaDevices.getUserMedia(constraints);\
        } else if (navigator.getUserMedia) {\
            return new Promise(function(resolve, reject) {\
                navigator.getUserMedia(constraints, resolve, reject);\
            });\
        } else {\
            return Promise.reject(new Error('\''getUserMedia tidak didukung di browser ini. Pastikan menggunakan HTTPS atau localhost.'\'));\
        }\
    }' "$FILE"

# Replace navigator.mediaDevices.getUserMedia dengan getUserMedia untuk checkin
sed -i 's/navigator\.mediaDevices\.getUserMedia({ video: { facingMode: '\''user'\'' } })/getUserMedia({ video: { facingMode: '\''user'\'' } })/g' "$FILE"

# Tambahkan pengecekan sebelum checkin_start_camera
sed -i '/document\.getElementById('\''checkin_start_camera'\'')\.addEventListener('\''click'\'', function() {/a\
        if (!navigator.mediaDevices && !navigator.getUserMedia) {\
            alert('\''Akses kamera tidak didukung di browser ini. Pastikan menggunakan HTTPS atau localhost.'\'');\
            return;\
        }' "$FILE"

# Tambahkan pengecekan sebelum checkout_start_camera
sed -i '/document\.getElementById('\''checkout_start_camera'\'')\.addEventListener('\''click'\'', function() {/a\
        if (!navigator.mediaDevices && !navigator.getUserMedia) {\
            alert('\''Akses kamera tidak didukung di browser ini. Pastikan menggunakan HTTPS atau localhost.'\'');\
            return;\
        }' "$FILE"

echo "✅ File fixed!"
echo "Backup saved to: ${FILE}.backup"
echo ""
echo "Clear view cache:"
echo "sudo -u www-data php artisan view:clear"

