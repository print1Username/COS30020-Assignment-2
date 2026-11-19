CREATE TABLE IF NOT EXISTS community_table (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	community_id INT NOT NULL,
	email VARCHAR(50) NULL,
	first_name VARCHAR(50) NOT NULL,
	last_name VARCHAR(50) NOT NULL,
	contribution_image VARCHAR(100) NULL,
	related_activity VARCHAR(50) NOT NULL,
	status VARCHAR(10) NOT NULL,
	title VARCHAR(100) NULL,
	description TEXT NULL,
	created_at DATE NULL,
	CONSTRAINT fk_community_post_info FOREIGN KEY ( community_id ) REFERENCES community_info( id ) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_community_post_user FOREIGN KEY ( email ) REFERENCES user_table( email ) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE = InnoDB;
