-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for absensi_ppkp
CREATE DATABASE IF NOT EXISTS `absensi_ppkp` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `absensi_ppkp`;

-- Dumping structure for table absensi_ppkp.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.cache: ~0 rows (approximately)

-- Dumping structure for table absensi_ppkp.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.cache_locks: ~0 rows (approximately)

-- Dumping structure for table absensi_ppkp.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.failed_jobs: ~0 rows (approximately)

-- Dumping structure for function absensi_ppkp.haversine_distance
DELIMITER //
CREATE FUNCTION `haversine_distance`(
    lat1 DECIMAL(10, 8),
    lng1 DECIMAL(11, 8),
    lat2 DECIMAL(10, 8),
    lng2 DECIMAL(11, 8)
) RETURNS decimal(10,2)
    READS SQL DATA
    DETERMINISTIC
BEGIN
    DECLARE earth_radius DECIMAL(10, 2) DEFAULT 6371000; 
    DECLARE dlat DECIMAL(10, 8);
    DECLARE dlng DECIMAL(11, 8);
    DECLARE a DECIMAL(20, 16);
    DECLARE c DECIMAL(20, 16);
    DECLARE distance DECIMAL(10, 2);
    
    
    SET lat1 = RADIANS(lat1);
    SET lng1 = RADIANS(lng1);
    SET lat2 = RADIANS(lat2);
    SET lng2 = RADIANS(lng2);
    
    
    SET dlat = lat2 - lat1;
    SET dlng = lng2 - lng1;
    
    
    SET a = SIN(dlat/2) * SIN(dlat/2) +
            COS(lat1) * COS(lat2) *
            SIN(dlng/2) * SIN(dlng/2);
    
    SET c = 2 * ATAN2(SQRT(a), SQRT(1-a));
    SET distance = earth_radius * c;
    
    RETURN distance;
END//
DELIMITER ;

-- Dumping structure for table absensi_ppkp.izin_cuti
CREATE TABLE IF NOT EXISTS `izin_cuti` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pegawai_id` bigint unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `jenis` enum('izin','cuti','sakit') COLLATE utf8mb4_unicode_ci DEFAULT 'izin',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','disetujui','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `disetujui_oleh` bigint unsigned DEFAULT NULL,
  `waktu_persetujuan` timestamp NULL DEFAULT NULL,
  `alasan_penolakan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `izin_cuti_disetujui_oleh_foreign` (`disetujui_oleh`),
  KEY `izin_cuti_pegawai_id_tanggal_index` (`pegawai_id`,`tanggal`),
  KEY `izin_cuti_status_index` (`status`),
  KEY `izin_cuti_tanggal_index` (`tanggal`),
  KEY `izin_cuti_jenis_index` (`jenis`),
  CONSTRAINT `izin_cuti_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `pegawai` (`id`) ON DELETE SET NULL,
  CONSTRAINT `izin_cuti_pegawai_id_foreign` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.izin_cuti: ~0 rows (approximately)

-- Dumping structure for table absensi_ppkp.jadwal_pegawai
CREATE TABLE IF NOT EXISTS `jadwal_pegawai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pegawai_id` bigint unsigned NOT NULL,
  `hari` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jam_masuk` time NOT NULL,
  `jam_keluar` time NOT NULL,
  `toleransi_telat` int NOT NULL DEFAULT '15' COMMENT 'Toleransi keterlambatan dalam menit',
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jadwal_pegawai_pegawai_id_index` (`pegawai_id`),
  KEY `jadwal_pegawai_is_aktif_index` (`is_aktif`),
  KEY `jadwal_pegawai_hari_index` (`hari`),
  KEY `jadwal_pegawai_tanggal_mulai_tanggal_selesai_index` (`tanggal_mulai`,`tanggal_selesai`),
  CONSTRAINT `jadwal_pegawai_pegawai_id_foreign` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.jadwal_pegawai: ~5 rows (approximately)
REPLACE INTO `jadwal_pegawai` (`id`, `pegawai_id`, `hari`, `jam_masuk`, `jam_keluar`, `toleransi_telat`, `is_aktif`, `tanggal_mulai`, `tanggal_selesai`, `created_at`, `updated_at`) VALUES
	(1, 4, 'Jumat', '07:30:00', '16:30:00', 0, 1, NULL, NULL, '2025-12-16 00:14:46', '2025-12-16 00:14:46'),
	(2, 4, 'Senin', '07:30:00', '16:00:00', 0, 1, NULL, NULL, '2025-12-16 00:15:17', '2025-12-16 00:15:17'),
	(3, 4, 'Selasa', '07:30:00', '16:00:00', 0, 1, NULL, NULL, '2025-12-16 00:15:17', '2025-12-16 00:15:17'),
	(4, 4, 'Rabu', '07:30:00', '16:00:00', 0, 1, NULL, NULL, '2025-12-16 00:15:17', '2025-12-16 00:15:17'),
	(5, 4, 'Kamis', '07:30:00', '16:00:00', 0, 1, NULL, NULL, '2025-12-16 00:15:17', '2025-12-16 00:15:17');

-- Dumping structure for table absensi_ppkp.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.jobs: ~0 rows (approximately)

-- Dumping structure for table absensi_ppkp.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.job_batches: ~0 rows (approximately)

-- Dumping structure for table absensi_ppkp.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.migrations: ~1 rows (approximately)
REPLACE INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2025_12_10_040525_create_personal_access_tokens_table', 1),
	(5, '2025_12_10_064608_create_satpelkes_table', 1),
	(6, '2025_12_10_064616_create_pegawai_table', 1),
	(7, '2025_12_10_064616_create_shift_table', 1),
	(8, '2025_12_10_064617_create_jadwal_pegawai_table', 1),
	(9, '2025_12_10_064618_create_presensi_table', 1),
	(10, '2025_12_10_064619_create_presensi_log_table', 1),
	(11, '2025_12_10_064619_create_tugas_luar_table', 1),
	(12, '2025_12_10_081652_add_dokumen_to_tugas_luar_table', 1),
	(13, '2025_12_10_084551_drop_shift_table_and_remove_shift_id_from_jadwal_pegawai', 1),
	(14, '2025_12_16_043518_create_izin_cuti_table', 1),
	(15, '2025_12_16_062351_add_sakit_to_izin_cuti_table', 1);

-- Dumping structure for table absensi_ppkp.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table absensi_ppkp.pegawai
CREATE TABLE IF NOT EXISTS `pegawai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nip` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `divisi` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jabatan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `satpelkes_id` bigint unsigned DEFAULT NULL,
  `device_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `role` enum('admin','pimpinan','pegawai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pegawai',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pegawai_nip_unique` (`nip`),
  UNIQUE KEY `pegawai_email_unique` (`email`),
  KEY `pegawai_status_index` (`status`),
  KEY `pegawai_role_index` (`role`),
  KEY `pegawai_satpelkes_id_index` (`satpelkes_id`),
  CONSTRAINT `pegawai_satpelkes_id_foreign` FOREIGN KEY (`satpelkes_id`) REFERENCES `satpelkes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.pegawai: ~4 rows (approximately)
REPLACE INTO `pegawai` (`id`, `nip`, `nama`, `email`, `password`, `divisi`, `jabatan`, `satpelkes_id`, `device_id`, `foto`, `status`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'ADM001', 'Admin Sistem', 'admin@ppkp.go.id', '$2y$12$Z0a3Bx.2Qde3Hq9oQoE15ezc/yqifvdZA2ygedImM1ZtQ3bdf5pAS', 'Jabatan Pelaksana', 'Administrator Sistem', 6, NULL, NULL, 'aktif', 'admin', NULL, '2025-12-15 23:40:50', '2025-12-16 00:06:54'),
	(2, 'KA-PPKP', 'dr. Dwian Andhika', 'pimpinan@ppkp.go.id', '$2y$12$Lf0XzBtaVdTAT4bangofG.NWOratUXvA2GPOlg0h0dKMNYJw.nHnu', 'Struktural', 'Kepala PPKP', 6, NULL, NULL, 'aktif', 'pimpinan', NULL, '2025-12-15 23:40:50', '2025-12-16 00:06:27'),
	(3, 'TU-PPKP', 'Apt. Dara Indri Yunita, S.Farm., M.Hum', 'pimpinan2@ppkp.go.id', '$2y$12$Lf0XzBtaVdTAT4bangofG.NWOratUXvA2GPOlg0h0dKMNYJw.nHnu', 'Struktural', 'Ka. Subbag TU', 6, NULL, NULL, 'aktif', 'pimpinan', NULL, '2025-12-15 23:40:50', '2025-12-16 00:06:31'),
	(4, '004199301017201710502', 'Fadillah Asseggaf', 'fadilasgaf93@gmail.com', '$2y$12$Lf0XzBtaVdTAT4bangofG.NWOratUXvA2GPOlg0h0dKMNYJw.nHnu', 'Jabatan Pelaksana', 'Pengolah Data', 6, NULL, NULL, 'aktif', 'pegawai', NULL, '2025-12-15 23:40:50', '2025-12-16 00:06:09');

-- Dumping structure for table absensi_ppkp.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.personal_access_tokens: ~0 rows (approximately)

-- Dumping structure for table absensi_ppkp.presensi
CREATE TABLE IF NOT EXISTS `presensi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pegawai_id` bigint unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `jenis` enum('check_in','check_out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu_absen` datetime NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `accuracy` decimal(10,2) NOT NULL COMMENT 'Akurasi GPS dalam meter',
  `device_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `satpelkes_id` bigint unsigned DEFAULT NULL,
  `jarak_ke_satpelkes` decimal(10,2) DEFAULT NULL COMMENT 'Jarak ke satpelkes dalam meter',
  `status` enum('IN_ZONE','OUT_ZONE_PENDING','APPROVED','REJECTED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IN_ZONE',
  `foto_asli` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path foto original',
  `foto_watermark` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path foto dengan watermark',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `presensi_pegawai_id_tanggal_index` (`pegawai_id`,`tanggal`),
  KEY `presensi_status_index` (`status`),
  KEY `presensi_tanggal_index` (`tanggal`),
  KEY `presensi_satpelkes_id_index` (`satpelkes_id`),
  KEY `presensi_jenis_index` (`jenis`),
  CONSTRAINT `presensi_pegawai_id_foreign` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
  CONSTRAINT `presensi_satpelkes_id_foreign` FOREIGN KEY (`satpelkes_id`) REFERENCES `satpelkes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.presensi: ~0 rows (approximately)

-- Dumping structure for table absensi_ppkp.presensi_log
CREATE TABLE IF NOT EXISTS `presensi_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `presensi_id` bigint unsigned NOT NULL,
  `pimpinan_id` bigint unsigned NOT NULL,
  `action` enum('approve','reject') COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `waktu_action` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `presensi_log_presensi_id_index` (`presensi_id`),
  KEY `presensi_log_pimpinan_id_index` (`pimpinan_id`),
  KEY `presensi_log_waktu_action_index` (`waktu_action`),
  CONSTRAINT `presensi_log_pimpinan_id_foreign` FOREIGN KEY (`pimpinan_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
  CONSTRAINT `presensi_log_presensi_id_foreign` FOREIGN KEY (`presensi_id`) REFERENCES `presensi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.presensi_log: ~0 rows (approximately)

-- Dumping structure for table absensi_ppkp.satpelkes
CREATE TABLE IF NOT EXISTS `satpelkes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_satpelkes` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_satpelkes` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius_absensi` int NOT NULL DEFAULT '100' COMMENT 'Radius absensi dalam meter',
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `satpelkes_kode_satpelkes_unique` (`kode_satpelkes`),
  KEY `satpelkes_is_aktif_index` (`is_aktif`),
  KEY `satpelkes_latitude_longitude_index` (`latitude`,`longitude`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.satpelkes: ~3 rows (approximately)
REPLACE INTO `satpelkes` (`id`, `nama_satpelkes`, `kode_satpelkes`, `latitude`, `longitude`, `radius_absensi`, `alamat`, `is_aktif`, `created_at`, `updated_at`) VALUES
	(6, 'Manajemen', '001', -6.18191530, 106.82915070, 50, 'Jl. Medan Merdeka Selatan No. 8-9 Blok E Lantai 2', 1, '2025-12-15 23:54:31', '2025-12-15 23:54:31'),
	(7, 'Klinik Utama Balaikota', '002', -6.18195980, 106.82914410, 50, 'Jl. Medan Medeka Selatan No. 8-9 Blok F Lantai 1', 1, '2025-12-15 23:56:05', '2025-12-15 23:56:05'),
	(8, 'Klinik Pratama Satpelkes Balaikota', '003', -6.18195980, 106.82914410, 100, 'Jl. Medan Merdeka Selatan No. 8-9 Blok E Lantai 2', 1, '2025-12-15 23:56:39', '2025-12-15 23:56:39');

-- Dumping structure for table absensi_ppkp.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.sessions: ~0 rows (approximately)

-- Dumping structure for procedure absensi_ppkp.sp_approve_presensi
DELIMITER //
CREATE PROCEDURE `sp_approve_presensi`(
    IN p_presensi_id BIGINT,
    IN p_pimpinan_id BIGINT,
    IN p_action ENUM('approve', 'reject'),
    IN p_catatan TEXT
)
BEGIN
    DECLARE v_status VARCHAR(20);
    DECLARE v_current_status VARCHAR(20);
    
    
    SELECT status INTO v_current_status
    FROM presensi
    WHERE id = p_presensi_id;
    
    IF v_current_status != 'OUT_ZONE_PENDING' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Presensi tidak dalam status OUT_ZONE_PENDING';
    END IF;
    
    
    IF p_action = 'approve' THEN
        SET v_status = 'APPROVED';
    ELSE
        SET v_status = 'REJECTED';
    END IF;
    
    
    UPDATE presensi
    SET status = v_status,
        updated_at = NOW()
    WHERE id = p_presensi_id;
    
    
    INSERT INTO presensi_log (
        presensi_id,
        pimpinan_id,
        action,
        catatan,
        waktu_action
    ) VALUES (
        p_presensi_id,
        p_pimpinan_id,
        p_action,
        p_catatan,
        NOW()
    );
END//
DELIMITER ;

-- Dumping structure for procedure absensi_ppkp.sp_laporan_tidak_masuk
DELIMITER //
CREATE PROCEDURE `sp_laporan_tidak_masuk`(
    IN p_tanggal_mulai DATE,
    IN p_tanggal_selesai DATE,
    IN p_pegawai_id BIGINT UNSIGNED
)
BEGIN
    SELECT 
        pg.id as pegawai_id,
        pg.nip,
        pg.nama,
        pg.divisi,
        pg.jabatan,
        c.nama_satpelkes,
        cal.tanggal,
        jp.hari,
        jp.jam_masuk,
        jp.jam_keluar,
        CASE 
            WHEN tl.id IS NOT NULL THEN CONCAT('Tugas Luar: ', tl.lokasi_tugas)
            WHEN iz.id IS NOT NULL THEN CONCAT('Izin: ', iz.jenis_izin)
            ELSE 'Tidak Masuk'
        END as keterangan,
        CASE 
            WHEN tl.id IS NOT NULL THEN 'tugas_luar'
            WHEN iz.id IS NOT NULL THEN 'izin'
            ELSE 'tanpa_keterangan'
        END as status_keterangan
    FROM pegawai pg
    INNER JOIN jadwal_pegawai jp ON pg.id = jp.pegawai_id 
        AND jp.is_aktif = TRUE
        AND (jp.tanggal_mulai IS NULL OR p_tanggal_selesai >= jp.tanggal_mulai)
        AND (jp.tanggal_selesai IS NULL OR p_tanggal_mulai <= jp.tanggal_selesai)
    LEFT JOIN satpelkes c ON pg.satpelkes_id = c.id
    CROSS JOIN (
        SELECT DATE_ADD(p_tanggal_mulai, INTERVAL seq.seq DAY) as tanggal
        FROM (
            SELECT 0 as seq UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION
            SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
            SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION
            SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
            SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION
            SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION
            SELECT 30 UNION SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION
            SELECT 35 UNION SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION
            SELECT 40 UNION SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION
            SELECT 45 UNION SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION
            SELECT 50 UNION SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION
            SELECT 55 UNION SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59 UNION
            SELECT 60 UNION SELECT 61 UNION SELECT 62 UNION SELECT 63 UNION SELECT 64 UNION
            SELECT 65 UNION SELECT 66 UNION SELECT 67 UNION SELECT 68 UNION SELECT 69 UNION
            SELECT 70 UNION SELECT 71 UNION SELECT 72 UNION SELECT 73 UNION SELECT 74 UNION
            SELECT 75 UNION SELECT 76 UNION SELECT 77 UNION SELECT 78 UNION SELECT 79 UNION
            SELECT 80 UNION SELECT 81 UNION SELECT 82 UNION SELECT 83 UNION SELECT 84 UNION
            SELECT 85 UNION SELECT 86 UNION SELECT 87 UNION SELECT 88 UNION SELECT 89 UNION
            SELECT 90 UNION SELECT 91 UNION SELECT 92 UNION SELECT 93 UNION SELECT 94 UNION
            SELECT 95 UNION SELECT 96 UNION SELECT 97 UNION SELECT 98 UNION SELECT 99
        ) seq
        HAVING tanggal <= p_tanggal_selesai
    ) cal
    LEFT JOIN presensi p ON pg.id = p.pegawai_id 
        AND cal.tanggal = p.tanggal 
        AND p.jenis = 'check_in'
    LEFT JOIN tugas_luar tl ON pg.id = tl.pegawai_id 
        AND cal.tanggal BETWEEN tl.tanggal_mulai AND tl.tanggal_selesai
        AND tl.status = 'disetujui'
    LEFT JOIN izin iz ON pg.id = iz.pegawai_id
        AND cal.tanggal BETWEEN iz.tanggal_mulai AND iz.tanggal_selesai
        AND iz.status = 'disetujui'
    WHERE cal.tanggal BETWEEN p_tanggal_mulai AND p_tanggal_selesai
        AND (jp.hari IS NULL OR DAYNAME(cal.tanggal) = jp.hari)
        AND p.id IS NULL 
        AND (p_pegawai_id IS NULL OR pg.id = p_pegawai_id) 
    ORDER BY cal.tanggal DESC, pg.nama;
END//
DELIMITER ;

-- Dumping structure for procedure absensi_ppkp.sp_process_checkin
DELIMITER //
CREATE PROCEDURE `sp_process_checkin`(
    IN p_pegawai_id BIGINT,
    IN p_jenis ENUM('check_in', 'check_out'),
    IN p_latitude DECIMAL(10, 8),
    IN p_longitude DECIMAL(11, 8),
    IN p_accuracy DECIMAL(10, 2),
    IN p_device_id VARCHAR(255),
    IN p_foto_asli VARCHAR(255),
    IN p_ip_address VARCHAR(45),
    IN p_user_agent TEXT,
    OUT p_presensi_id BIGINT,
    OUT p_status VARCHAR(20),
    OUT p_satpelkes_id BIGINT,
    OUT p_jarak DECIMAL(10, 2)
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_satpelkes_id BIGINT;
    DECLARE v_latitude DECIMAL(10, 8);
    DECLARE v_longitude DECIMAL(11, 8);
    DECLARE v_radius INT;
    DECLARE v_jarak DECIMAL(10, 2);
    DECLARE v_jarak_terdekat DECIMAL(10, 2) DEFAULT 99999999.99; 
    DECLARE v_satpelkes_terdekat BIGINT DEFAULT NULL;
    DECLARE v_status VARCHAR(20) DEFAULT 'OUT_ZONE_PENDING';
    
    
    DECLARE cur_satpelkes CURSOR FOR
        SELECT id, latitude, longitude, radius_absensi
        FROM satpelkes
        WHERE is_aktif = TRUE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    
    OPEN cur_satpelkes;
    
    read_loop: LOOP
        FETCH cur_satpelkes INTO v_satpelkes_id, v_latitude, v_longitude, v_radius;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        
        SET v_jarak = haversine_distance(
            p_latitude, 
            p_longitude, 
            v_latitude, 
            v_longitude
        );
        
        
        IF v_jarak <= v_radius THEN
            
            SET v_status = 'IN_ZONE';
            SET v_satpelkes_terdekat = v_satpelkes_id;
            SET v_jarak_terdekat = v_jarak;
            LEAVE read_loop; 
        ELSE
            
            IF v_jarak < v_jarak_terdekat THEN
                SET v_jarak_terdekat = v_jarak;
                SET v_satpelkes_terdekat = v_satpelkes_id;
            END IF;
        END IF;
    END LOOP;
    
    CLOSE cur_satpelkes;
    
    
    INSERT INTO presensi (
        pegawai_id,
        tanggal,
        jenis,
        waktu_absen,
        latitude,
        longitude,
        accuracy,
        device_id,
        satpelkes_id,
        jarak_ke_satpelkes,
        status,
        foto_asli,
        ip_address,
        user_agent
    ) VALUES (
        p_pegawai_id,
        CURDATE(),
        p_jenis,
        NOW(),
        p_latitude,
        p_longitude,
        p_accuracy,
        p_device_id,
        v_satpelkes_terdekat,
        v_jarak_terdekat,
        v_status,
        p_foto_asli,
        p_ip_address,
        p_user_agent
    );
    
    SET p_presensi_id = LAST_INSERT_ID();
    SET p_status = v_status;
    SET p_satpelkes_id = v_satpelkes_terdekat;
    SET p_jarak = v_jarak_terdekat;
END//
DELIMITER ;

-- Dumping structure for table absensi_ppkp.tugas_luar
CREATE TABLE IF NOT EXISTS `tugas_luar` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pegawai_id` bigint unsigned NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `lokasi_tugas` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `dokumen` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path dokumen bukti surat tugas',
  `status` enum('pending','disetujui','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `disetujui_oleh` bigint unsigned DEFAULT NULL,
  `waktu_persetujuan` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tugas_luar_disetujui_oleh_foreign` (`disetujui_oleh`),
  KEY `tugas_luar_pegawai_id_index` (`pegawai_id`),
  KEY `tugas_luar_status_index` (`status`),
  KEY `tugas_luar_tanggal_mulai_tanggal_selesai_index` (`tanggal_mulai`,`tanggal_selesai`),
  CONSTRAINT `tugas_luar_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `pegawai` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tugas_luar_pegawai_id_foreign` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.tugas_luar: ~0 rows (approximately)

-- Dumping structure for table absensi_ppkp.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table absensi_ppkp.users: ~0 rows (approximately)

-- Dumping structure for view absensi_ppkp.v_laporan_kehadiran
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_laporan_kehadiran` (
	`presensi_id` BIGINT(20) UNSIGNED NOT NULL,
	`pegawai_id` BIGINT(20) UNSIGNED NOT NULL,
	`nip` VARCHAR(191) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nama` VARCHAR(191) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`divisi` VARCHAR(191) NULL COLLATE 'utf8mb4_unicode_ci',
	`jabatan` VARCHAR(191) NULL COLLATE 'utf8mb4_unicode_ci',
	`nama_satpelkes` VARCHAR(191) NULL COLLATE 'utf8mb4_unicode_ci',
	`tanggal` DATE NOT NULL,
	`jam_masuk` TIME NULL,
	`jam_keluar` TIME NULL,
	`waktu_checkin` DATETIME NOT NULL,
	`jam_checkin` TIME NULL,
	`menit_telat` BIGINT(19) NULL,
	`status_kehadiran` VARCHAR(16) NOT NULL COLLATE 'cp850_general_ci',
	`status_absensi` ENUM('IN_ZONE','OUT_ZONE_PENDING','APPROVED','REJECTED') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`jarak_ke_satpelkes` DECIMAL(10,2) NULL COMMENT 'Jarak ke satpelkes dalam meter',
	`foto_watermark` VARCHAR(191) NULL COMMENT 'Path foto dengan watermark' COLLATE 'utf8mb4_unicode_ci',
	`satpelkes_id` BIGINT(20) UNSIGNED NULL
) ENGINE=MyISAM;

-- Dumping structure for view absensi_ppkp.v_laporan_telat
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_laporan_telat` (
	`presensi_id` BIGINT(20) UNSIGNED NOT NULL,
	`pegawai_id` BIGINT(20) UNSIGNED NOT NULL,
	`nip` VARCHAR(191) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nama` VARCHAR(191) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`divisi` VARCHAR(191) NULL COLLATE 'utf8mb4_unicode_ci',
	`jabatan` VARCHAR(191) NULL COLLATE 'utf8mb4_unicode_ci',
	`nama_satpelkes` VARCHAR(191) NULL COLLATE 'utf8mb4_unicode_ci',
	`tanggal` DATE NOT NULL,
	`jam_masuk` TIME NULL,
	`jam_keluar` TIME NULL,
	`waktu_checkin` DATETIME NOT NULL,
	`jam_checkin` TIME NULL,
	`menit_telat` BIGINT(19) NULL,
	`status_kehadiran` VARCHAR(16) NOT NULL COLLATE 'cp850_general_ci',
	`status_absensi` ENUM('IN_ZONE','OUT_ZONE_PENDING','APPROVED','REJECTED') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`jarak_ke_satpelkes` DECIMAL(10,2) NULL COMMENT 'Jarak ke satpelkes dalam meter',
	`foto_watermark` VARCHAR(191) NULL COMMENT 'Path foto dengan watermark' COLLATE 'utf8mb4_unicode_ci',
	`satpelkes_id` BIGINT(20) UNSIGNED NULL
) ENGINE=MyISAM;

-- Dumping structure for view absensi_ppkp.v_laporan_kehadiran
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_laporan_kehadiran`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_laporan_kehadiran` AS select `p`.`id` AS `presensi_id`,`p`.`pegawai_id` AS `pegawai_id`,`pg`.`nip` AS `nip`,`pg`.`nama` AS `nama`,`pg`.`divisi` AS `divisi`,`pg`.`jabatan` AS `jabatan`,`c`.`nama_satpelkes` AS `nama_satpelkes`,`p`.`tanggal` AS `tanggal`,`jp`.`jam_masuk` AS `jam_masuk`,`jp`.`jam_keluar` AS `jam_keluar`,`p`.`waktu_absen` AS `waktu_checkin`,cast(`p`.`waktu_absen` as time) AS `jam_checkin`,(case when ((`jp`.`id` is not null) and (cast(`p`.`waktu_absen` as time) > addtime(`jp`.`jam_masuk`,sec_to_time((`jp`.`toleransi_telat` * 60))))) then timestampdiff(MINUTE,addtime(`jp`.`jam_masuk`,sec_to_time((`jp`.`toleransi_telat` * 60))),cast(`p`.`waktu_absen` as time)) else 0 end) AS `menit_telat`,(case when (`jp`.`id` is null) then 'Tidak Ada Jadwal' when (cast(`p`.`waktu_absen` as time) > addtime(`jp`.`jam_masuk`,sec_to_time((`jp`.`toleransi_telat` * 60)))) then 'Telat' when (`p`.`status` = 'REJECTED') then 'Ditolak' when (`p`.`status` = 'OUT_ZONE_PENDING') then 'Pending Approval' when (`p`.`status` = 'APPROVED') then 'Approved' else 'Tepat Waktu' end) AS `status_kehadiran`,`p`.`status` AS `status_absensi`,`p`.`jarak_ke_satpelkes` AS `jarak_ke_satpelkes`,`p`.`foto_watermark` AS `foto_watermark`,`p`.`satpelkes_id` AS `satpelkes_id` from (((`presensi` `p` join `pegawai` `pg` on((`p`.`pegawai_id` = `pg`.`id`))) left join `satpelkes` `c` on((`p`.`satpelkes_id` = `c`.`id`))) left join `jadwal_pegawai` `jp` on(((`pg`.`id` = `jp`.`pegawai_id`) and ((`jp`.`hari` is null) or (dayname(`p`.`tanggal`) = `jp`.`hari`)) and (`jp`.`is_aktif` = true) and ((`jp`.`tanggal_mulai` is null) or (`p`.`tanggal` >= `jp`.`tanggal_mulai`)) and ((`jp`.`tanggal_selesai` is null) or (`p`.`tanggal` <= `jp`.`tanggal_selesai`))))) where (`p`.`jenis` = 'check_in');

-- Dumping structure for view absensi_ppkp.v_laporan_telat
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_laporan_telat`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_laporan_telat` AS select `lp`.`presensi_id` AS `presensi_id`,`lp`.`pegawai_id` AS `pegawai_id`,`lp`.`nip` AS `nip`,`lp`.`nama` AS `nama`,`lp`.`divisi` AS `divisi`,`lp`.`jabatan` AS `jabatan`,`lp`.`nama_satpelkes` AS `nama_satpelkes`,`lp`.`tanggal` AS `tanggal`,`lp`.`jam_masuk` AS `jam_masuk`,`lp`.`jam_keluar` AS `jam_keluar`,`lp`.`waktu_checkin` AS `waktu_checkin`,`lp`.`jam_checkin` AS `jam_checkin`,`lp`.`menit_telat` AS `menit_telat`,`lp`.`status_kehadiran` AS `status_kehadiran`,`lp`.`status_absensi` AS `status_absensi`,`lp`.`jarak_ke_satpelkes` AS `jarak_ke_satpelkes`,`lp`.`foto_watermark` AS `foto_watermark`,`lp`.`satpelkes_id` AS `satpelkes_id` from `v_laporan_kehadiran` `lp` where (`lp`.`menit_telat` > 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
