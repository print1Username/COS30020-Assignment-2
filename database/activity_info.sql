CREATE TABLE IF NOT EXISTS activity_info (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    source_id INT NULL UNIQUE,
    title VARCHAR(100) NULL,
    activity_type VARCHAR(50) NULL,
    description TEXT NULL,
    date DATE NULL,
    time TIME NULL,
    end_time TIME NULL,
    price DECIMAL(8, 2) NULL,
    venue VARCHAR(100) NULL,
    table_name VARCHAR(50) NULL,
    image VARCHAR(100) NULL,
    status VARCHAR(10) NOT NULL
) ENGINE=InnoDB;
