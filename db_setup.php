<?php
$host = "localhost";
$dbName = "cos30020a2";
$dbUser = "root";
$dbPass = "";

try {
	// Connect to MySQL server
	$pdo = new PDO(
		"mysql:host=$host;charset=utf8mb4",
		$dbUser,
		$dbPass,
		[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
	);

	// Create database automatically
	$pdo -> exec("
        CREATE DATABASE IF NOT EXISTS `$dbName`
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci
    ");

	// Select database
	$pdo -> exec("USE `$dbName`");

	// 1. User table
	$pdo -> exec("
        CREATE TABLE IF NOT EXISTS user_table (
            email VARCHAR(50) NOT NULL PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            dob DATE NULL,
            gender VARCHAR(6) NOT NULL,
            hometown VARCHAR(50) NOT NULL,
            profile_image VARCHAR(100) NULL,
            resume_file VARCHAR(100) NULL
        ) ENGINE=InnoDB
    ");

	// 2. Account table
	$pdo -> exec("
        CREATE TABLE IF NOT EXISTS account_table (
            email VARCHAR(50) NOT NULL PRIMARY KEY,
            password VARCHAR(255) NOT NULL,
            type VARCHAR(5) NOT NULL,
            FOREIGN KEY (email)
                REFERENCES user_table(email)
                ON UPDATE CASCADE
                ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

	$accounts = json_decode(file_get_contents(__DIR__."/data/accounts.json"), true);
	$insertDefaultUser = $pdo -> prepare("INSERT IGNORE INTO user_table (email, first_name, last_name, dob, gender, hometown) VALUES (?, ?, ?, ?, ?, ?)");
	$insertDefaultAccount = $pdo -> prepare("INSERT IGNORE INTO account_table (email, password, type) VALUES (?, ?, ?)");
	$seedAccounts = (int)$pdo->query("SELECT COUNT(*) FROM account_table")->fetchColumn() === 0;
	foreach ($seedAccounts ? $accounts : [] as $account) {
		$insertDefaultUser -> execute([
			$account["email"], $account["first_name"], $account["last_name"],
			$account["dob"], $account["gender"], $account["hometown"]
		]);
		$insertDefaultAccount -> execute([
			$account["email"], password_hash($account["password"], PASSWORD_DEFAULT), $account["type"]
		]);
	}

	// 3. Smart feature table
	$pdo -> exec("
        CREATE TABLE IF NOT EXISTS smart_feature_table (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            item_name VARCHAR(50) NOT NULL,
            item_category VARCHAR(50) NOT NULL,
            item_image VARCHAR(100) NULL,
            description_file VARCHAR(100) NULL,
            status VARCHAR(10) NOT NULL
        ) ENGINE=InnoDB
    ");

	// 4. Activity table
	$pdo -> exec("
        CREATE TABLE IF NOT EXISTS activity_table (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(50) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            date DATE NULL,
            time TIME NULL,
            contact_number VARCHAR(15) NULL,
            status VARCHAR(10) NOT NULL,
            record_type VARCHAR(12) NOT NULL DEFAULT 'registration',
            source_id INT NULL UNIQUE,
            activity_id INT NULL,
            title VARCHAR(100) NULL,
            activity_type VARCHAR(50) NULL,
            description TEXT NULL,
            end_time TIME NULL,
            price DECIMAL(8, 2) NULL,
            venue VARCHAR(100) NULL,
            table_name VARCHAR(50) NULL,
            image VARCHAR(100) NULL
        ) ENGINE=InnoDB
    ");

	// Add Part 3 columns to databases created by the earlier version.
	$requiredColumns = [
		"user_table" => ["resume_file" => "VARCHAR(100) NULL"],
		"activity_table" => [
			"record_type" => "VARCHAR(12) NOT NULL DEFAULT 'registration'",
			"source_id" => "INT NULL UNIQUE",
			"activity_id" => "INT NULL",
			"title" => "VARCHAR(100) NULL",
			"activity_type" => "VARCHAR(50) NULL",
			"description" => "TEXT NULL",
			"end_time" => "TIME NULL",
			"price" => "DECIMAL(8, 2) NULL",
			"venue" => "VARCHAR(100) NULL",
			"table_name" => "VARCHAR(50) NULL",
			"image" => "VARCHAR(100) NULL"
		]
	];
	$columnCheck = $pdo -> prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");
	foreach ($requiredColumns as $table => $columns) {
		foreach ($columns as $column => $definition) {
			$columnCheck -> execute([$dbName, $table, $column]);
			if (!$columnCheck -> fetchColumn()) {
				$pdo -> exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
			}
		}
	}

	$activities = json_decode(file_get_contents(__DIR__."/data/activities.json"), true);
	$insertActivity = $pdo -> prepare("INSERT IGNORE INTO activity_table (email, first_name, last_name, date, time, status, record_type, source_id, title, activity_type, description, end_time, price, venue, table_name, image) VALUES ('', '', '', ?, ?, 'approved', 'event', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$seedActivities = (int)$pdo->query("SELECT COUNT(*) FROM activity_table")->fetchColumn() === 0;
	foreach ($seedActivities ? $activities : [] as $seedActivity) {
		$insertActivity -> execute([
			$seedActivity["date"], $seedActivity["start_time"], $seedActivity["id"],
			$seedActivity["title"], $seedActivity["type"], $seedActivity["description"],
			$seedActivity["end_time"], $seedActivity["price"], $seedActivity["venue"],
			$seedActivity["table"], $seedActivity["image"]
		]);
	}

	// 5. Community table
	$pdo -> exec("
        CREATE TABLE IF NOT EXISTS community_table (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            contribution_image VARCHAR(100) NULL,
            related_activity VARCHAR(50) NOT NULL,
            status VARCHAR(10) NOT NULL,
            source_id INT NULL UNIQUE,
            title VARCHAR(100) NULL,
            description TEXT NULL,
            member_count INT NULL,
            created_at DATE NULL,
            contribution_title VARCHAR(100) NULL,
            contribution_description TEXT NULL,
            contribution_date DATE NULL
        ) ENGINE=InnoDB
    ");

	$communityColumns = [
		"source_id" => "INT NULL UNIQUE",
		"title" => "VARCHAR(100) NULL",
		"description" => "TEXT NULL",
		"member_count" => "INT NULL",
		"created_at" => "DATE NULL",
		"contribution_title" => "VARCHAR(100) NULL",
		"contribution_description" => "TEXT NULL",
		"contribution_date" => "DATE NULL"
	];
	foreach ($communityColumns as $column => $definition) {
		$columnCheck -> execute([$dbName, "community_table", $column]);
		if (!$columnCheck -> fetchColumn()) {
			$pdo -> exec("ALTER TABLE `community_table` ADD COLUMN `$column` $definition");
		}
	}

	$communities = json_decode(file_get_contents(__DIR__."/data/community.json"), true);
	$insertCommunity = $pdo -> prepare("INSERT IGNORE INTO community_table (first_name, last_name, contribution_image, related_activity, status, source_id, title, description, member_count, created_at, contribution_title, contribution_description, contribution_date) VALUES (?, ?, ?, ?, 'approved', ?, ?, ?, ?, ?, ?, ?, ?)");
	$seedCommunities = (int)$pdo->query("SELECT COUNT(*) FROM community_table")->fetchColumn() === 0;
	foreach ($seedCommunities ? $communities : [] as $seedCommunity) {
		$contribution = $seedCommunity["contributions"][0] ?? [];
		$name = explode(" ", $contribution["contributor"] ?? "", 2);
		$insertCommunity -> execute([
			$name[0] ?? "", $name[1] ?? "", $contribution["media"] ?? null,
			$contribution["activity"] ?? "", $seedCommunity["id"],
			$seedCommunity["title"], $seedCommunity["description"],
			$seedCommunity["member_count"], $seedCommunity["created_at"],
			$contribution["title"] ?? null, $contribution["description"] ?? null,
			$contribution["created_at"] ?? null
		]);
	}

} catch (PDOException $e) {
	error_log($e -> getMessage());
	http_response_code(500);
	exit("Database setup failed.");
}
?>
