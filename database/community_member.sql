CREATE TABLE IF NOT EXISTS community_member (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	community_id INT NOT NULL,
	email VARCHAR(50) NOT NULL,
	status VARCHAR(10) NOT NULL,
	CONSTRAINT unique_community_member UNIQUE (community_id, email),
	CONSTRAINT fk_community_member_info FOREIGN KEY (community_id) REFERENCES community_info(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_community_member_user FOREIGN KEY (email) REFERENCES user_table(email) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB;
