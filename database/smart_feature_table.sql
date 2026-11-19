CREATE TABLE IF NOT EXISTS smart_feature_table (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	item_name VARCHAR(50) NOT NULL,
	item_category VARCHAR(50) NOT NULL,
	item_image VARCHAR(100) NULL,
	description_file VARCHAR(100) NULL,
	status VARCHAR(10) NOT NULL
) ENGINE = InnoDB;
