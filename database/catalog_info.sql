CREATE TABLE IF NOT EXISTS catalog_info (
	id INT NOT NULL PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	categories JSON NOT NULL,
	description TEXT NOT NULL,
	players VARCHAR(50) NOT NULL,
	duration VARCHAR(50) NOT NULL,
	image VARCHAR(255) NULL,
	image_data LONGBLOB NULL,
	image_mime VARCHAR(30) NULL,
	status VARCHAR(10) NOT NULL DEFAULT 'approved'
) ENGINE = InnoDB;
