-- Set MySQL timezone ke Asia/Jakarta (WIB = UTC+7)
-- Jalankan file ini untuk mengatur timezone MySQL secara global

-- Set timezone untuk session saat ini
SET time_zone = '+07:00';

-- Set timezone global (memerlukan SUPER privilege)
-- SET GLOBAL time_zone = '+07:00';

-- Verifikasi timezone
SELECT @@session.time_zone AS session_timezone, @@global.time_zone AS global_timezone, NOW() AS current_time;

