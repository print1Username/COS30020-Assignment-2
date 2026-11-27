CREATE TABLE IF NOT EXISTS smart_feature_table (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	item_name VARCHAR(50) NOT NULL,
	item_category VARCHAR(50) NOT NULL,
	item_image VARCHAR(100) NULL,
	description_file VARCHAR(100) NULL,
	status VARCHAR(10) NOT NULL,
	catalog_id INT NULL UNIQUE,
	item_description TEXT NULL,
	players VARCHAR(50) NULL,
	duration VARCHAR(50) NULL,
	CONSTRAINT fk_smart_feature_catalog
		FOREIGN KEY (catalog_id) REFERENCES catalog_info(id) ON DELETE CASCADE
) ENGINE = InnoDB;
