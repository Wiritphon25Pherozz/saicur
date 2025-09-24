-- /api/migrate.sql
CREATE TABLE IF NOT EXISTS cards (
  uid VARCHAR(32) PRIMARY KEY,
  balance INT NOT NULL DEFAULT 0,
  version INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  uid VARCHAR(32),
  action VARCHAR(32),
  delta INT NULL,
  balance_after INT NULL,
  request_id INT NULL,
  applied VARCHAR(32) NULL,
  ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_logs_uid_ts (uid, ts)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  uid VARCHAR(32) NOT NULL,
  req_by INT NULL,
  amount INT NOT NULL,
  type ENUM('topup','manual_adjust') NOT NULL DEFAULT 'topup',
  status ENUM('pending','approved','rejected','applied') NOT NULL DEFAULT 'pending',
  admin_id INT NULL,
  admin_note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pending_writes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  uid VARCHAR(32),
  newBlock4 VARCHAR(32),
  request_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  claimed_at TIMESTAMP NULL,
  claimed_by VARCHAR(128) NULL,
  INDEX (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
