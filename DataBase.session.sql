USE athena;

--@block
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'member') NOT NULL DEFAULT 'member',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);