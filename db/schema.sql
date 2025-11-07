CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  apellido VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  sexo ENUM('M','F','X') DEFAULT 'X',
  password_hash VARCHAR(255) NOT NULL,
  HashMagic VARCHAR(255) NULL,          
  secret_2fa VARCHAR(255) NULL,         
  twofa_enabled TINYINT(1) NOT NULL DEFAULT 0,      
  twofa_last_verified_at DATETIME NULL,             
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
