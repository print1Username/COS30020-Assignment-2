CREATE TABLE IF NOT EXISTS user_table (
	email VARCHAR(50) NOT NULL PRIMARY KEY,
	first_name VARCHAR(50) NOT NULL,
	last_name VARCHAR(50) NOT NULL,
	dob DATE NULL,
	gender VARCHAR(6) NOT NULL,
	hometown VARCHAR(50) NOT NULL,
	profile_image VARCHAR(255) NULL,
	resume_file VARCHAR(100) NULL,
	resume_filename VARCHAR(255) NULL
) ENGINE = InnoDB;
