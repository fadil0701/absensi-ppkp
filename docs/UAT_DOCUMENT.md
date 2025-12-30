# Dokumen User Acceptance Test (UAT)
## Sistem Absensi PPKP

---

## Informasi Dokumen

| Item | Keterangan |
|------|------------|
| **Nama Sistem** | Sistem Absensi PPKP |
| **Versi Dokumen** | 1.0 |
| **Tanggal Dokumen** | 30 Desember 2025 |
| **Disusun Oleh** | Tim Development |
| **Status** | Draft |

---

## Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Tujuan UAT](#tujuan-uat)
3. [Ruang Lingkup](#ruang-lingkup)
4. [Test Cases](#test-cases)
5. [Kriteria Penerimaan](#kriteria-penerimaan)
6. [Hasil Testing](#hasil-testing)
7. [Sign-off](#sign-off)

---

## Pendahuluan

Dokumen User Acceptance Test (UAT) ini digunakan untuk menguji dan memverifikasi bahwa Sistem Absensi PPKP telah memenuhi kebutuhan dan spesifikasi yang ditetapkan. UAT dilakukan oleh pengguna akhir (end user) untuk memastikan sistem dapat digunakan sesuai dengan kebutuhan bisnis.

### Fitur Utama Sistem

1. **Autentikasi & Authorization** - Login, Logout, Role-based access
2. **Dashboard** - Ringkasan data absensi
3. **Manajemen Pegawai** - CRUD data pegawai
4. **Manajemen Unit Kerja** - CRUD satuan pelayanan kesehatan
5. **Jadwal Pegawai** - Pengaturan jadwal kerja pegawai
6. **Absensi** - Check-in dan Check-out pegawai
7. **Tugas Luar** - Pengajuan dan approval tugas luar
8. **Izin/Cuti** - Pengajuan izin, cuti, dan sakit
9. **Approval** - Persetujuan presensi, tugas luar, dan izin
10. **Riwayat Presensi** - Lihat riwayat absensi pegawai
11. **Laporan** - Laporan telat, tidak masuk, dan akumulasi
12. **API Key Management** - Manajemen kunci API untuk integrasi
13. **Profile** - Pengelolaan profil pengguna
14. **API Integration** - Endpoint untuk integrasi dengan sistem lain

---

## Tujuan UAT

1. Memverifikasi bahwa semua fitur berfungsi sesuai dengan spesifikasi
2. Memastikan sistem dapat digunakan dengan mudah oleh end user
3. Mengidentifikasi bug atau masalah yang perlu diperbaiki
4. Memastikan sistem memenuhi kebutuhan bisnis
5. Mendapatkan persetujuan dari stakeholder untuk go-live

---

## Ruang Lingkup

### Modul yang Diuji

- ✅ Autentikasi (Login/Logout)
- ✅ Dashboard
- ✅ Manajemen Pegawai
- ✅ Manajemen Unit Kerja
- ✅ Jadwal Pegawai
- ✅ Absensi (Check-in/Check-out)
- ✅ Tugas Luar
- ✅ Izin/Cuti
- ✅ Approval
- ✅ Riwayat Presensi
- ✅ Laporan
- ✅ API Key Management
- ✅ Profile
- ✅ API Integration

### Role yang Diuji

- **Admin** - Akses penuh ke semua fitur
- **Pimpinan** - Akses ke fitur manajemen dan approval
- **Pegawai** - Akses ke fitur absensi dan profile

---

## Test Cases

### TC-001: Autentikasi - Login

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-001 |
| **Nama Test** | Login dengan kredensial valid |
| **Precondition** | User memiliki akun yang aktif |
| **Test Steps** | 1. Buka halaman login<br>2. Masukkan email dan password yang valid<br>3. Klik tombol "Login" |
| **Expected Result** | User berhasil login dan diarahkan ke dashboard |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-002 |
| **Nama Test** | Login dengan kredensial tidak valid |
| **Precondition** | User memiliki akun |
| **Test Steps** | 1. Buka halaman login<br>2. Masukkan email atau password yang salah<br>3. Klik tombol "Login" |
| **Expected Result** | Sistem menampilkan pesan error "Email atau password salah" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-003 |
| **Nama Test** | Logout |
| **Precondition** | User sudah login |
| **Test Steps** | 1. Klik tombol "Logout" di navbar<br>2. Konfirmasi logout |
| **Expected Result** | User berhasil logout dan diarahkan ke halaman login |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-004: Dashboard

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-004 |
| **Nama Test** | Menampilkan dashboard dengan data statistik |
| **Precondition** | User sudah login |
| **Test Steps** | 1. Akses halaman dashboard<br>2. Periksa data yang ditampilkan |
| **Expected Result** | Dashboard menampilkan:<br>- Total pegawai<br>- Total presensi hari ini<br>- Total pegawai yang belum absen<br>- Grafik statistik |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-005: Manajemen Pegawai

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-005 |
| **Nama Test** | Menampilkan daftar pegawai |
| **Precondition** | User login sebagai Admin/Pimpinan |
| **Test Steps** | 1. Klik menu "Pegawai"<br>2. Periksa daftar pegawai yang ditampilkan |
| **Expected Result** | Sistem menampilkan tabel daftar pegawai dengan kolom: NIP, Nama, Email, Jabatan, Unit Kerja, Status |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-006 |
| **Nama Test** | Menambah pegawai baru |
| **Precondition** | User login sebagai Admin/Pimpinan |
| **Test Steps** | 1. Klik menu "Pegawai"<br>2. Klik tombol "Tambah Pegawai"<br>3. Isi form dengan data lengkap<br>4. Klik tombol "Simpan" |
| **Expected Result** | Pegawai baru berhasil ditambahkan dan muncul di daftar |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-007 |
| **Nama Test** | Mengedit data pegawai |
| **Precondition** | User login sebagai Admin/Pimpinan, ada data pegawai |
| **Test Steps** | 1. Klik menu "Pegawai"<br>2. Klik tombol "Edit" pada salah satu pegawai<br>3. Ubah data yang diperlukan<br>4. Klik tombol "Update" |
| **Expected Result** | Data pegawai berhasil diupdate |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-008 |
| **Nama Test** | Menghapus pegawai |
| **Precondition** | User login sebagai Admin/Pimpinan, ada data pegawai |
| **Test Steps** | 1. Klik menu "Pegawai"<br>2. Klik tombol "Hapus" pada salah satu pegawai<br>3. Konfirmasi penghapusan |
| **Expected Result** | Pegawai berhasil dihapus dari sistem |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-009: Manajemen Unit Kerja (Satpelkes)

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-009 |
| **Nama Test** | Menampilkan daftar unit kerja |
| **Precondition** | User login sebagai Admin/Pimpinan |
| **Test Steps** | 1. Klik menu "Unit Kerja"<br>2. Periksa daftar unit kerja |
| **Expected Result** | Sistem menampilkan tabel daftar unit kerja |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-010 |
| **Nama Test** | Menambah unit kerja baru |
| **Precondition** | User login sebagai Admin/Pimpinan |
| **Test Steps** | 1. Klik menu "Unit Kerja"<br>2. Klik tombol "Tambah Unit Kerja"<br>3. Isi form dengan data lengkap<br>4. Klik tombol "Simpan" |
| **Expected Result** | Unit kerja baru berhasil ditambahkan |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-011: Jadwal Pegawai

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-011 |
| **Nama Test** | Menampilkan daftar jadwal pegawai |
| **Precondition** | User login sebagai Admin/Pimpinan |
| **Test Steps** | 1. Klik menu "Jadwal Pegawai"<br>2. Periksa daftar jadwal |
| **Expected Result** | Sistem menampilkan daftar jadwal pegawai |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-012 |
| **Nama Test** | Menambah jadwal pegawai |
| **Precondition** | User login sebagai Admin/Pimpinan, ada data pegawai |
| **Test Steps** | 1. Klik menu "Jadwal Pegawai"<br>2. Klik tombol "Tambah Jadwal"<br>3. Pilih pegawai, hari, jam masuk, jam keluar<br>4. Klik tombol "Simpan" |
| **Expected Result** | Jadwal pegawai berhasil ditambahkan |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-013 |
| **Nama Test** | Menambah jadwal bulk untuk beberapa pegawai |
| **Precondition** | User login sebagai Admin/Pimpinan, ada beberapa data pegawai |
| **Test Steps** | 1. Klik menu "Jadwal Pegawai"<br>2. Klik tombol "Tambah Bulk"<br>3. Pilih beberapa pegawai, hari, jam masuk, jam keluar<br>4. Klik tombol "Simpan" |
| **Expected Result** | Jadwal untuk beberapa pegawai berhasil ditambahkan sekaligus |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-014: Absensi - Check-in

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-014 |
| **Nama Test** | Check-in dalam zona |
| **Precondition** | User login sebagai Pegawai, ada jadwal untuk hari ini, user berada dalam radius zona absensi |
| **Test Steps** | 1. Klik menu "Absensi"<br>2. Klik tombol "Check-in"<br>3. Konfirmasi lokasi |
| **Expected Result** | Check-in berhasil, sistem menampilkan waktu check-in dan status "IN_ZONE" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-015 |
| **Nama Test** | Check-in di luar zona |
| **Precondition** | User login sebagai Pegawai, ada jadwal untuk hari ini, user berada di luar radius zona absensi |
| **Test Steps** | 1. Klik menu "Absensi"<br>2. Klik tombol "Check-in"<br>3. Konfirmasi lokasi |
| **Expected Result** | Check-in berhasil dengan status "OUT_OF_ZONE", memerlukan approval |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-016 |
| **Nama Test** | Check-in terlambat |
| **Precondition** | User login sebagai Pegawai, ada jadwal untuk hari ini, waktu check-in melebihi jam masuk yang ditentukan |
| **Test Steps** | 1. Klik menu "Absensi"<br>2. Klik tombol "Check-in" setelah jam masuk |
| **Expected Result** | Check-in berhasil, sistem mencatat keterlambatan |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-017: Absensi - Check-out

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-017 |
| **Nama Test** | Check-out normal |
| **Precondition** | User sudah melakukan check-in hari ini |
| **Test Steps** | 1. Klik menu "Absensi"<br>2. Klik tombol "Check-out"<br>3. Konfirmasi lokasi |
| **Expected Result** | Check-out berhasil, sistem menampilkan waktu check-out |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-018 |
| **Nama Test** | Check-out lebih awal (pulang cepat) |
| **Precondition** | User sudah melakukan check-in hari ini, waktu check-out sebelum jam keluar yang ditentukan |
| **Test Steps** | 1. Klik menu "Absensi"<br>2. Klik tombol "Check-out" sebelum jam keluar |
| **Expected Result** | Check-out berhasil, sistem mencatat pulang cepat |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-019: Tugas Luar

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-019 |
| **Nama Test** | Menampilkan daftar tugas luar |
| **Precondition** | User sudah login |
| **Test Steps** | 1. Klik menu "Tugas Luar"<br>2. Periksa daftar tugas luar |
| **Expected Result** | Sistem menampilkan daftar tugas luar dengan status (pending, approved, rejected) |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-020 |
| **Nama Test** | Menambah tugas luar baru |
| **Precondition** | User login sebagai Pegawai |
| **Test Steps** | 1. Klik menu "Tugas Luar"<br>2. Klik tombol "Tambah Tugas Luar"<br>3. Isi form: tanggal, lokasi, keterangan<br>4. Klik tombol "Simpan" |
| **Expected Result** | Tugas luar berhasil ditambahkan dengan status "pending" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-021 |
| **Nama Test** | Approval tugas luar (Approve) |
| **Precondition** | User login sebagai Admin/Pimpinan, ada tugas luar dengan status "pending" |
| **Test Steps** | 1. Klik menu "Tugas Luar" > "Pending"<br>2. Klik tombol "Approve" pada salah satu tugas luar<br>3. Konfirmasi approval |
| **Expected Result** | Status tugas luar berubah menjadi "approved" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-022 |
| **Nama Test** | Approval tugas luar (Reject) |
| **Precondition** | User login sebagai Admin/Pimpinan, ada tugas luar dengan status "pending" |
| **Test Steps** | 1. Klik menu "Tugas Luar" > "Pending"<br>2. Klik tombol "Reject" pada salah satu tugas luar<br>3. Masukkan alasan penolakan<br>4. Konfirmasi rejection |
| **Expected Result** | Status tugas luar berubah menjadi "rejected" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-023: Izin/Cuti

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-023 |
| **Nama Test** | Menambah izin baru |
| **Precondition** | User login sebagai Admin/Pimpinan |
| **Test Steps** | 1. Klik menu "Izin/Cuti" (jika ada) atau melalui menu lain<br>2. Klik tombol "Tambah Izin"<br>3. Pilih pegawai, jenis (Izin/Cuti/Sakit), tanggal, keterangan<br>4. Klik tombol "Simpan" |
| **Expected Result** | Izin berhasil ditambahkan dengan status "pending" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-024 |
| **Nama Test** | Approval izin (Approve) |
| **Precondition** | User login sebagai Admin/Pimpinan, ada izin dengan status "pending" |
| **Test Steps** | 1. Akses halaman approval izin<br>2. Klik tombol "Approve" pada salah satu izin<br>3. Konfirmasi approval |
| **Expected Result** | Status izin berubah menjadi "approved" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-025: Approval Presensi

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-025 |
| **Nama Test** | Menampilkan daftar presensi pending |
| **Precondition** | User login sebagai Admin/Pimpinan, ada presensi dengan status "OUT_OF_ZONE" |
| **Test Steps** | 1. Klik menu "Approval"<br>2. Periksa daftar presensi pending |
| **Expected Result** | Sistem menampilkan daftar presensi yang memerlukan approval |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-026 |
| **Nama Test** | Approval presensi (Approve) |
| **Precondition** | User login sebagai Admin/Pimpinan, ada presensi dengan status "OUT_OF_ZONE" |
| **Test Steps** | 1. Klik menu "Approval"<br>2. Klik tombol "Approve" pada salah satu presensi<br>3. Konfirmasi approval |
| **Expected Result** | Status presensi berubah menjadi "APPROVED" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-027 |
| **Nama Test** | Approval presensi (Reject) |
| **Precondition** | User login sebagai Admin/Pimpinan, ada presensi dengan status "OUT_OF_ZONE" |
| **Test Steps** | 1. Klik menu "Approval"<br>2. Klik tombol "Reject" pada salah satu presensi<br>3. Masukkan alasan penolakan<br>4. Konfirmasi rejection |
| **Expected Result** | Status presensi berubah menjadi "REJECTED" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-028: Riwayat Presensi

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-028 |
| **Nama Test** | Menampilkan riwayat presensi |
| **Precondition** | User sudah login |
| **Test Steps** | 1. Klik menu "Riwayat Presensi"<br>2. Periksa daftar riwayat presensi |
| **Expected Result** | Sistem menampilkan riwayat presensi dengan filter tanggal, pegawai (jika Admin/Pimpinan) |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-029 |
| **Nama Test** | Melihat detail presensi |
| **Precondition** | User sudah login, ada data presensi |
| **Test Steps** | 1. Klik menu "Riwayat Presensi"<br>2. Klik salah satu presensi untuk melihat detail |
| **Expected Result** | Sistem menampilkan detail presensi: tanggal, waktu check-in, waktu check-out, lokasi, status, foto (jika ada) |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-030: Laporan - Telat

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-030 |
| **Nama Test** | Menampilkan laporan keterlambatan |
| **Precondition** | User login sebagai Admin/Pimpinan |
| **Test Steps** | 1. Klik menu "Laporan"<br>2. Klik "Laporan Telat" atau akses langsung<br>3. Pilih filter tanggal (opsional)<br>4. Klik tombol "Filter" |
| **Expected Result** | Sistem menampilkan tabel laporan pegawai yang terlambat dengan kolom: NIP, Nama, Tanggal, Jam Masuk Seharusnya, Jam Masuk Aktual, Keterlambatan |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-031 |
| **Nama Test** | Print laporan telat |
| **Precondition** | User login sebagai Admin/Pimpinan, ada data laporan telat |
| **Test Steps** | 1. Akses laporan telat<br>2. Klik tombol "Print" |
| **Expected Result** | Laporan telat tercetak dengan format tabel yang rapi, termasuk header dan footer |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-032: Laporan - Tidak Masuk

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-032 |
| **Nama Test** | Menampilkan laporan tidak masuk |
| **Precondition** | User login sebagai Admin/Pimpinan |
| **Test Steps** | 1. Klik menu "Laporan"<br>2. Klik "Laporan Tidak Masuk"<br>3. Pilih filter tanggal (opsional)<br>4. Klik tombol "Filter" |
| **Expected Result** | Sistem menampilkan tabel laporan pegawai yang tidak masuk dengan kolom: NIP, Nama, Tanggal, Keterangan (Izin/Cuti/Sakit/Tugas Luar) |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-033 |
| **Nama Test** | Print laporan tidak masuk |
| **Precondition** | User login sebagai Admin/Pimpinan, ada data laporan tidak masuk |
| **Test Steps** | 1. Akses laporan tidak masuk<br>2. Klik tombol "Print" |
| **Expected Result** | Laporan tidak masuk tercetak dengan format tabel yang rapi, termasuk header dan footer |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-034: Laporan - Akumulasi

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-034 |
| **Nama Test** | Menampilkan laporan akumulasi bulanan |
| **Precondition** | User login sebagai Admin/Pimpinan |
| **Test Steps** | 1. Klik menu "Laporan" > "Akumulasi"<br>2. Pilih bulan (format: YYYY-MM)<br>3. Pilih pegawai (opsional)<br>4. Klik tombol "Filter" |
| **Expected Result** | Sistem menampilkan tabel akumulasi dengan kolom:<br>- NIP, Nama, Jabatan, Unit Kerja<br>- Total Absensi<br>- Keterlambatan (menit dan jumlah)<br>- Pulang Cepat (menit dan jumlah)<br>- Tidak Masuk<br>- Cuti, Izin, Sakit<br>- Tugas Luar<br>- % Kehadiran |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-035 |
| **Nama Test** | Print laporan akumulasi |
| **Precondition** | User login sebagai Admin/Pimpinan, ada data laporan akumulasi |
| **Test Steps** | 1. Akses laporan akumulasi<br>2. Klik tombol "Print" |
| **Expected Result** | Laporan akumulasi tercetak dengan format tabel yang rapi, termasuk header dan footer |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-036: API Key Management

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-036 |
| **Nama Test** | Menampilkan daftar API Key |
| **Precondition** | User login sebagai Admin |
| **Test Steps** | 1. Klik menu "API Key Management"<br>2. Periksa daftar API Key |
| **Expected Result** | Sistem menampilkan tabel daftar API Key dengan kolom: Nama, API Key, Webhook URL, Rate Limit, Status, Terakhir Digunakan, Kadaluarsa |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-037 |
| **Nama Test** | Menambah API Key baru |
| **Precondition** | User login sebagai Admin |
| **Test Steps** | 1. Klik menu "API Key Management"<br>2. Klik tombol "Tambah API Key"<br>3. Isi form: Nama, Deskripsi, Webhook URL (opsional), Allowed IPs (opsional), Scopes (opsional), Rate Limit, Expires At (opsional)<br>4. Klik tombol "Simpan" |
| **Expected Result** | API Key berhasil dibuat, sistem menampilkan:<br>- API Key<br>- ConsID (Consumer ID)<br>- UserKey<br>- Secret Key<br>**PENTING**: Secret Key hanya ditampilkan sekali |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-038 |
| **Nama Test** | Melihat detail API Key |
| **Precondition** | User login sebagai Admin, ada data API Key |
| **Test Steps** | 1. Klik menu "API Key Management"<br>2. Klik tombol "Detail" pada salah satu API Key |
| **Expected Result** | Sistem menampilkan detail lengkap API Key: Nama, API Key, ConsID, UserKey, Deskripsi, Webhook URL, Allowed IPs, Scopes, Rate Limit, Status, Kadaluarsa, Terakhir Digunakan |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-039 |
| **Nama Test** | Regenerate Secret Key |
| **Precondition** | User login sebagai Admin, ada data API Key |
| **Test Steps** | 1. Akses detail API Key<br>2. Klik tombol "Regenerate Secret Key"<br>3. Konfirmasi regenerate |
| **Expected Result** | Secret Key baru berhasil di-generate dan ditampilkan (hanya sekali), Secret Key lama tidak bisa digunakan lagi |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-040 |
| **Nama Test** | Menghapus API Key |
| **Precondition** | User login sebagai Admin, ada data API Key |
| **Test Steps** | 1. Akses detail API Key<br>2. Klik tombol "Hapus API Key"<br>3. Konfirmasi penghapusan |
| **Expected Result** | API Key berhasil dihapus dari sistem |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-041: Profile

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-041 |
| **Nama Test** | Menampilkan profile pengguna |
| **Precondition** | User sudah login |
| **Test Steps** | 1. Klik menu "Profile Pegawai"<br>2. Periksa data profile |
| **Expected Result** | Sistem menampilkan data profile: NIP, Nama, Email, Jabatan, Unit Kerja, Foto (jika ada) |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-042 |
| **Nama Test** | Mengedit profile |
| **Precondition** | User sudah login |
| **Test Steps** | 1. Klik menu "Profile Pegawai"<br>2. Klik tombol "Edit"<br>3. Ubah data yang diizinkan (misalnya foto)<br>4. Klik tombol "Update" |
| **Expected Result** | Profile berhasil diupdate |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-043: API Integration - Autentikasi dengan API Key

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-043 |
| **Nama Test** | Mengakses endpoint dengan API Key |
| **Precondition** | Ada API Key yang valid dan aktif |
| **Test Steps** | 1. Buat request ke endpoint `/api/integration/akumulasi?bulan=2025-12`<br>2. Tambahkan header: `X-API-KEY` dan `X-SECRET-KEY` |
| **Expected Result** | Request berhasil, sistem mengembalikan data JSON |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-044 |
| **Nama Test** | Mengakses endpoint dengan ConsID & UserKey |
| **Precondition** | Ada API Key yang valid dengan ConsID dan UserKey |
| **Test Steps** | 1. Buat request ke endpoint `/api/integration/akumulasi?bulan=2025-12`<br>2. Tambahkan header: `X-CONSID`, `X-USERKEY`, dan `X-SECRET-KEY` |
| **Expected Result** | Request berhasil, sistem mengembalikan data JSON |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-045 |
| **Nama Test** | Mengakses endpoint tanpa autentikasi |
| **Precondition** | - |
| **Test Steps** | 1. Buat request ke endpoint `/api/integration/akumulasi?bulan=2025-12`<br>2. Tanpa header autentikasi |
| **Expected Result** | Request gagal, sistem mengembalikan error 401 "API Key or ConsID/UserKey is required" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-046 |
| **Nama Test** | Mengakses endpoint dengan API Key tidak valid |
| **Precondition** | - |
| **Test Steps** | 1. Buat request ke endpoint `/api/integration/akumulasi?bulan=2025-12`<br>2. Tambahkan header: `X-API-KEY` dengan nilai yang tidak valid |
| **Expected Result** | Request gagal, sistem mengembalikan error 401 "Invalid API Key" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-047 |
| **Nama Test** | Mengakses endpoint dengan IP tidak diizinkan |
| **Precondition** | Ada API Key dengan IP whitelist yang tidak termasuk IP penguji |
| **Test Steps** | 1. Buat request ke endpoint `/api/integration/akumulasi?bulan=2025-12`<br>2. Tambahkan header: `X-API-KEY` dan `X-SECRET-KEY` yang valid<br>3. Request dari IP yang tidak diizinkan |
| **Expected Result** | Request gagal, sistem mengembalikan error 403 "IP address not allowed" |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-048: API Integration - Endpoint Akumulasi

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-048 |
| **Nama Test** | Mengambil data akumulasi bulanan |
| **Precondition** | Ada API Key yang valid, ada data presensi |
| **Test Steps** | 1. Buat request GET ke `/api/integration/akumulasi?bulan=2025-12`<br>2. Tambahkan header autentikasi yang valid<br>3. Periksa response |
| **Expected Result** | Sistem mengembalikan JSON dengan struktur:<br>```json<br>{<br>  "success": true,<br>  "data": [...],<br>  "meta": {...}<br>}<br>``` |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-049: API Integration - Endpoint Presensi Harian

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-049 |
| **Nama Test** | Mengambil data presensi harian |
| **Precondition** | Ada API Key yang valid, ada data presensi |
| **Test Steps** | 1. Buat request GET ke `/api/integration/presensi-harian?tanggal=2025-12-30`<br>2. Tambahkan header autentikasi yang valid<br>3. Periksa response |
| **Expected Result** | Sistem mengembalikan JSON dengan data presensi harian |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

### TC-050: Role-based Access Control

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-050 |
| **Nama Test** | Pegawai tidak bisa akses menu Admin |
| **Precondition** | User login sebagai Pegawai |
| **Test Steps** | 1. Coba akses URL langsung: `/pegawai`, `/satpelkes`, `/laporan`, `/api-keys` |
| **Expected Result** | Sistem menolak akses (403 Forbidden atau redirect) |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

| Item | Keterangan |
|------|------------|
| **Test Case ID** | TC-051 |
| **Nama Test** | Pimpinan bisa akses menu manajemen tapi tidak bisa akses API Key Management |
| **Precondition** | User login sebagai Pimpinan |
| **Test Steps** | 1. Coba akses menu: `/pegawai`, `/satpelkes`, `/laporan` (harus bisa)<br>2. Coba akses menu: `/api-keys` (harus ditolak) |
| **Expected Result** | Menu manajemen bisa diakses, menu API Key Management ditolak |
| **Actual Result** | |
| **Status** | ⬜ Pass / ⬜ Fail |
| **Tester** | |
| **Tanggal Test** | |
| **Catatan** | |

---

## Kriteria Penerimaan

### Kriteria Umum

1. ✅ Semua fitur utama berfungsi sesuai dengan spesifikasi
2. ✅ Tidak ada bug critical yang menghalangi penggunaan sistem
3. ✅ Sistem dapat diakses dengan mudah oleh end user
4. ✅ Performa sistem memadai (response time < 3 detik untuk operasi normal)
5. ✅ Data tersimpan dengan benar dan konsisten
6. ✅ Validasi input berfungsi dengan baik
7. ✅ Error handling memberikan pesan yang jelas
8. ✅ Security: Role-based access control berfungsi dengan baik
9. ✅ API Integration berfungsi dengan baik
10. ✅ Laporan dapat dicetak dengan format yang rapi

### Kriteria Khusus per Modul

#### Autentikasi
- ✅ Login berfungsi dengan kredensial valid
- ✅ Login gagal dengan kredensial tidak valid
- ✅ Logout berfungsi dengan baik
- ✅ Session management berfungsi dengan baik

#### Absensi
- ✅ Check-in/Check-out berfungsi dengan baik
- ✅ Validasi zona absensi berfungsi
- ✅ Pencatatan keterlambatan dan pulang cepat akurat
- ✅ Foto absensi tersimpan dengan baik (jika ada)

#### Laporan
- ✅ Laporan telat menampilkan data yang akurat
- ✅ Laporan tidak masuk menampilkan data yang akurat
- ✅ Laporan akumulasi menampilkan data yang akurat
- ✅ Print laporan menghasilkan format yang rapi

#### API Integration
- ✅ Autentikasi dengan API Key berfungsi
- ✅ Autentikasi dengan ConsID/UserKey berfungsi
- ✅ IP whitelist berfungsi
- ✅ Rate limiting berfungsi (jika diimplementasikan)
- ✅ Endpoint mengembalikan data yang benar

---

## Hasil Testing

### Ringkasan Hasil

| Kategori | Total Test Cases | Pass | Fail | Pass Rate |
|----------|------------------|------|------|-----------|
| Autentikasi | 3 | | | % |
| Dashboard | 1 | | | % |
| Manajemen Pegawai | 4 | | | % |
| Manajemen Unit Kerja | 2 | | | % |
| Jadwal Pegawai | 3 | | | % |
| Absensi | 5 | | | % |
| Tugas Luar | 4 | | | % |
| Izin/Cuti | 2 | | | % |
| Approval | 3 | | | % |
| Riwayat Presensi | 2 | | | % |
| Laporan | 6 | | | % |
| API Key Management | 5 | | | % |
| Profile | 2 | | | % |
| API Integration | 7 | | | % |
| Role-based Access | 2 | | | % |
| **TOTAL** | **51** | | | **%** |

### Daftar Bug/Issues

| No | Test Case ID | Deskripsi Bug | Severity | Status | Catatan |
|----|--------------|---------------|----------|--------|---------|
| 1 | | | | | |
| 2 | | | | | |
| 3 | | | | | |

**Severity:**
- **Critical**: Bug yang menghalangi penggunaan fitur utama
- **High**: Bug yang mempengaruhi fungsionalitas penting
- **Medium**: Bug yang mempengaruhi fungsionalitas minor
- **Low**: Bug kosmetik atau enhancement

---

## Sign-off

### Persetujuan UAT

| Role | Nama | Jabatan | Tanda Tangan | Tanggal |
|------|------|---------|-------------|---------|
| **Project Manager** | | | | |
| **Business Analyst** | | | | |
| **End User (Admin)** | | | | |
| **End User (Pimpinan)** | | | | |
| **End User (Pegawai)** | | | | |
| **IT Manager** | | | | |

### Catatan Sign-off

**Status UAT:** ⬜ **APPROVED** / ⬜ **REJECTED** / ⬜ **CONDITIONAL APPROVAL**

**Kondisi (jika Conditional Approval):**
- 

**Catatan Tambahan:**
- 

---

## Lampiran

### A. Daftar Endpoint API

#### Public Endpoints
- `POST /api/auth/login` - Login

#### API Key Protected Endpoints
- `GET /api/integration/akumulasi` - Data akumulasi bulanan
- `GET /api/integration/presensi-harian` - Data presensi harian
- `GET /api/integration/export` - Export data

#### Sanctum Protected Endpoints
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Get current user
- `POST /api/presensi/check-in` - Check-in
- `POST /api/presensi/check-out` - Check-out
- `GET /api/presensi/riwayat` - Riwayat presensi
- `GET /api/presensi/{id}` - Detail presensi
- `GET /api/presensi/approval/pending` - Daftar pending approval
- `POST /api/presensi/approval/approve` - Approve presensi
- `POST /api/presensi/approval/reject` - Reject presensi
- `GET /api/jadwal/pegawai/{pegawai_id}` - Get jadwal pegawai
- `POST /api/jadwal/pegawai` - Create jadwal
- `GET /api/laporan/telat` - Laporan telat
- `GET /api/laporan/tidak-masuk` - Laporan tidak masuk
- `GET /api/laporan/dashboard` - Dashboard laporan
- `GET /api/satpelkes` - Daftar satpelkes

### B. Daftar Role dan Permission

| Role | Permission |
|------|------------|
| **Admin** | Akses penuh ke semua fitur |
| **Pimpinan** | Akses ke manajemen pegawai, unit kerja, jadwal, approval, laporan |
| **Pegawai** | Akses ke absensi, tugas luar, riwayat presensi, profile |

### C. Daftar Browser yang Didukung

- ✅ Google Chrome (Latest)
- ✅ Mozilla Firefox (Latest)
- ✅ Microsoft Edge (Latest)
- ✅ Safari (Latest)

### D. Kontak Support

- **Email:** support@example.com
- **Phone:** +62-xxx-xxxx-xxxx
- **Documentation:** `/docs/INTEGRATION_GUIDE.md`

---

**Dokumen ini adalah dokumen hidup dan akan diperbarui sesuai dengan hasil testing yang dilakukan.**

---

*Dokumen UAT v1.0 - 30 Desember 2025*

