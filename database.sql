-- Kas Warga (MySQL) — import via phpMyAdmin
CREATE DATABASE IF NOT EXISTS kas_warga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kas_warga;

CREATE TABLE IF NOT EXISTS warga (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  alamat VARCHAR(200) NOT NULL,
  no_hp VARCHAR(30) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS iuran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  warga_id INT NOT NULL,
  bulan CHAR(7) NOT NULL,
  nominal INT NOT NULL,
  status ENUM('LUNAS','BELUM') NOT NULL DEFAULT 'BELUM',
  keterangan VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_iuran_warga FOREIGN KEY (warga_id) REFERENCES warga(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_warga_bulan (warga_id, bulan)
) ENGINE=InnoDB;

INSERT INTO warga(nama, alamat, no_hp) VALUES
('Rubi', 'Blok A No.3', '081234567890'),
('Alya', 'Blok B No.5', '081223344556'),
('Doni', 'Blok C No.1', '081298765432');

INSERT INTO iuran(warga_id, bulan, nominal, status, keterangan) VALUES
(1,'2025-09',100000,'LUNAS','Transfer'),
(2,'2025-09',100000,'BELUM',NULL),
(3,'2025-09',100000,'LUNAS','Tunai'),
(1,'2025-10',100000,'BELUM',NULL);
