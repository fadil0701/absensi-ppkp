-- Fix stored procedure: Ubah default value v_jarak_terdekat
-- DECIMAL(10, 2) maksimal = 99999999.99
-- Default 999999999 terlalu besar

DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_process_checkin`$$

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
    DECLARE v_jarak_terdekat DECIMAL(10, 2) DEFAULT 99999999.99; -- FIX: Ubah dari 999999999 ke 99999999.99
    DECLARE v_satpelkes_terdekat BIGINT DEFAULT NULL;
    DECLARE v_status VARCHAR(20) DEFAULT 'OUT_ZONE_PENDING';
    
    -- Cursor untuk iterasi semua satpelkes aktif
    DECLARE cur_satpelkes CURSOR FOR
        SELECT id, latitude, longitude, radius_absensi
        FROM satpelkes
        WHERE is_aktif = TRUE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Iterasi semua satpelkes menggunakan CURSOR
    OPEN cur_satpelkes;
    
    read_loop: LOOP
        FETCH cur_satpelkes INTO v_satpelkes_id, v_latitude, v_longitude, v_radius;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Hitung jarak ke satpelkes ini menggunakan fungsi Haversine
        SET v_jarak = haversine_distance(
            p_latitude, 
            p_longitude, 
            v_latitude, 
            v_longitude
        );
        
        -- Cek apakah dalam radius
        IF v_jarak <= v_radius THEN
            -- Dalam zona, set status IN_ZONE
            SET v_status = 'IN_ZONE';
            SET v_satpelkes_terdekat = v_satpelkes_id;
            SET v_jarak_terdekat = v_jarak;
            LEAVE read_loop; -- Keluar dari loop, sudah ditemukan zona
        ELSE
            -- Di luar zona, cari yang terdekat untuk referensi
            IF v_jarak < v_jarak_terdekat THEN
                SET v_jarak_terdekat = v_jarak;
                SET v_satpelkes_terdekat = v_satpelkes_id;
            END IF;
        END IF;
    END LOOP;
    
    CLOSE cur_satpelkes;
    
    -- Insert presensi
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
END$$

DELIMITER ;


