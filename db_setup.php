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
	require_once __DIR__."/upload_helpers.php";
	$schemaFiles = [
		"homepage_image_table.sql",
		"user_table.sql",
		"account_table.sql",
		"smart_feature_table.sql",
		"activity_info.sql",
		"activity_table.sql",
		"community_table.sql"
	];
	foreach ($schemaFiles as $schemaFile) {
		$sql = file_get_contents(__DIR__."/database/".$schemaFile);
		if ($sql === false) {
			throw new RuntimeException("Database schema file could not be read: ".$schemaFile);
		}
		$pdo -> exec($sql);
	}

	// Homepage slideshow images
	$insertHomepageImage = $pdo -> prepare("INSERT IGNORE INTO homepage_image_table (filename, mime_type, image_data) VALUES (?, ?, ?)");
	for ($imageNumber = 1; $imageNumber <= 5; $imageNumber++) {
		$imagePath = __DIR__."/img/boardgame-".$imageNumber.".jpg";
		if (is_file($imagePath)) {
			$insertHomepageImage -> execute([
				"boardgame-".$imageNumber.".jpg",
				"image/jpeg",
				file_get_contents($imagePath)
			]);
		}
	}

	$accounts = json_decode(file_get_contents(__DIR__."/data/accounts.json"), true);
	$insertDefaultUser = $pdo -> prepare("INSERT IGNORE INTO user_table (email, first_name, last_name, dob, gender, hometown) VALUES (?, ?, ?, ?, ?, ?)");
	$insertDefaultAccount = $pdo -> prepare("INSERT IGNORE INTO account_table (email, password, type) VALUES (?, ?, ?)");
	$seedAccounts = (int)$pdo -> query("SELECT COUNT(*) FROM account_table") -> fetchColumn() === 0;
	foreach ($seedAccounts ? $accounts : [] as $account) {
		$insertDefaultUser -> execute([
			$account["email"], $account["first_name"], $account["last_name"],
			$account["dob"], $account["gender"], $account["hometown"]
		]);
		$insertDefaultAccount -> execute([
			$account["email"], password_hash($account["password"], PASSWORD_DEFAULT), $account["type"]
		]);
	}

	$sampleGames = [
		["Catan", "Strategy", "img/boardgame-1.jpg", "smart_feature_description/sample-catan.pdf"],
		["Ticket to Ride", "Family Games", "img/boardgame-2.jpg", "smart_feature_description/sample-ticket-to-ride.pdf"],
		["Carcassonne", "Strategy", "img/boardgame-3.jpg", "smart_feature_description/sample-carcassonne.pdf"],
		["7 Wonders", "Card Games", "img/boardgame-4.jpg", "smart_feature_description/sample-7-wonders.pdf"]
	];
	$insertSampleGame = $pdo -> prepare("INSERT INTO smart_feature_table (item_name, item_category, item_image, description_file, status)
		SELECT ?, ?, ?, ?, 'approved' WHERE NOT EXISTS
		(SELECT 1 FROM smart_feature_table WHERE item_name = ?)");
	foreach ($sampleGames as $game) {
		$insertSampleGame -> execute([...$game, $game[0]]);
	}

	// Add Part 3 columns to databases created by the earlier version.
	$requiredColumns = [
		"user_table" => [
			"resume_file" => "VARCHAR(100) NULL",
			"resume_filename" => "VARCHAR(255) NULL",
			"profile_image" => "VARCHAR(255) NULL",
			"profile_image_data" => "LONGBLOB NULL",
			"profile_image_mime" => "VARCHAR(30) NULL"
		],
		"activity_table" => ["activity_id" => "INT NULL"]
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
	$pdo -> exec("ALTER TABLE user_table MODIFY profile_image VARCHAR(255) NULL");

	$columnCheck -> execute([$dbName, "activity_table", "record_type"]);
	if ($columnCheck -> fetchColumn()) {
		$invalidRegistrations = $pdo -> query("SELECT COUNT(*) FROM activity_table AS registration LEFT JOIN user_table AS user ON user.email = registration.email LEFT JOIN activity_table AS event ON event.id = registration.activity_id AND event.record_type = 'event' WHERE registration.record_type = 'registration' AND NOT (registration.id = 21 AND registration.email = 'wdawda@fefsef.com' AND registration.activity_id IS NULL) AND (user.email IS NULL OR event.id IS NULL)") -> fetchColumn();
		if ((int)$invalidRegistrations > 0) {
			throw new RuntimeException("Activity registrations must reference an existing user and event before migration.");
		}
		$migrationSql = file_get_contents(__DIR__."/database/activity_migration.sql");
		if ($migrationSql === false) {
			throw new RuntimeException("Activity migration SQL could not be read.");
		}
		foreach (explode(";", $migrationSql) as $migrationStatement) {
			if (trim($migrationStatement) !== "") {
				$pdo -> exec($migrationStatement);
			}
		}
	}
	$pdo -> exec("ALTER TABLE activity_table MODIFY activity_id INT NOT NULL");
	$columnCheck -> execute([$dbName, "activity_info", "source_id"]);
	if ($columnCheck -> fetchColumn()) {
		$pdo -> exec("ALTER TABLE activity_info DROP COLUMN source_id");
	}
	$activityForeignKeys = $pdo -> prepare("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'activity_table' AND REFERENCED_TABLE_NAME IS NOT NULL");
	$activityForeignKeys -> execute([$dbName]);
	$existingActivityKeys = $activityForeignKeys -> fetchAll(PDO::FETCH_COLUMN);
	if (!in_array("fk_activity_user", $existingActivityKeys, true)) {
		$pdo -> exec("ALTER TABLE activity_table ADD CONSTRAINT fk_activity_user FOREIGN KEY (email) REFERENCES user_table(email) ON UPDATE CASCADE ON DELETE CASCADE");
	}
	if (!in_array("fk_activity_info", $existingActivityKeys, true)) {
		$pdo -> exec("ALTER TABLE activity_table ADD CONSTRAINT fk_activity_info FOREIGN KEY (activity_id) REFERENCES activity_info(id) ON UPDATE CASCADE ON DELETE CASCADE");
	}

	$defaultProfileImages = [
		"admin@swin.edu.my" => "admin.png",
		"user1@swin.edu.my" => "user1.png",
		"user2@swin.edu.my" => "user2.png"
	];
	$setDefaultProfileImage = $pdo -> prepare("UPDATE user_table SET profile_image = ? WHERE email = ? AND (profile_image IS NULL OR profile_image = '' OR profile_image = ?)");
	$defaultImageExists = $pdo -> prepare("SELECT COUNT(*) FROM user_table WHERE email = ? AND profile_image_data IS NOT NULL AND OCTET_LENGTH(profile_image_data) > 0");
	$setDefaultImageData = $pdo -> prepare("UPDATE user_table SET profile_image_data = ?, profile_image_mime = 'image/png' WHERE email = ? AND (profile_image_data IS NULL OR OCTET_LENGTH(profile_image_data) = 0)");
	foreach ($defaultProfileImages as $seedEmail => $filename) {
		$relativeImagePath = "profile_images/".$seedEmail."/".$filename;
		$setDefaultProfileImage -> execute([$relativeImagePath, $seedEmail, "profile_images/".$filename]);
		$defaultImageExists -> execute([$seedEmail]);
		if ($defaultImageExists -> fetchColumn()) {
			continue;
		}
		$imagePath = __DIR__."/".$relativeImagePath;
		if (!is_file($imagePath) || (new finfo(FILEINFO_MIME_TYPE)) -> file($imagePath) !== "image/png") {
			throw new RuntimeException("Default profile image is missing or invalid: ".$filename);
		}
		$defaultImage = file_get_contents($imagePath);
		$setDefaultImageData -> execute([$defaultImage, $seedEmail]);
	}

	$activities = json_decode(file_get_contents(__DIR__."/data/activities.json"), true);
	$insertActivity = $pdo -> prepare("INSERT IGNORE INTO activity_info (id, date, time, status, title, activity_type, description, end_time, price, venue, table_name, image) VALUES (?, ?, ?, 'approved', ?, ?, ?, ?, ?, ?, ?, ?)");
	$seedActivities = (int)$pdo -> query("SELECT COUNT(*) FROM activity_info") -> fetchColumn() === 0;
	foreach ($seedActivities ? $activities : [] as $seedActivity) {
		$insertActivity -> execute([
			$seedActivity["id"], $seedActivity["date"], $seedActivity["start_time"],
			$seedActivity["title"], $seedActivity["type"], $seedActivity["description"],
			$seedActivity["end_time"], $seedActivity["price"], $seedActivity["venue"],
			$seedActivity["table"], $seedActivity["image"]
		]);
	}

	$communityColumns = [
		"source_id" => "INT NULL UNIQUE",
		"title" => "VARCHAR(100) NULL",
		"description" => "TEXT NULL",
		"member_count" => "INT NULL",
		"created_at" => "DATE NULL",
		"contribution_title" => "VARCHAR(100) NULL",
		"contribution_description" => "TEXT NULL",
		"contribution_date" => "DATE NULL",
		"record_type" => "VARCHAR(20) NOT NULL DEFAULT 'community'",
		"community_id" => "INT NULL",
		"email" => "VARCHAR(50) NULL",
		"member_counted" => "TINYINT(1) NOT NULL DEFAULT 0"
	];
	foreach ($communityColumns as $column => $definition) {
		$columnCheck -> execute([$dbName, "community_table", $column]);
		if (!$columnCheck -> fetchColumn()) {
			$pdo -> exec("ALTER TABLE `community_table` ADD COLUMN `$column` $definition");
		}
	}
	$pdo -> exec("UPDATE community_table SET record_type = 'community' WHERE record_type IS NULL OR record_type = ''");
	$joinIndex = $pdo -> prepare("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = ? AND table_name = 'community_table' AND index_name = 'unique_community_join'");
	$joinIndex -> execute([$dbName]);
	if (!$joinIndex -> fetchColumn()) {
		$pdo -> exec("ALTER TABLE community_table ADD UNIQUE KEY unique_community_join (community_id, email, record_type)");
	}

	$communities = json_decode(file_get_contents(__DIR__."/data/community.json"), true);
	$insertCommunity = $pdo -> prepare("INSERT IGNORE INTO community_table (first_name, last_name, contribution_image, related_activity, status, source_id, title, description, member_count, created_at, contribution_title, contribution_description, contribution_date) VALUES (?, ?, ?, ?, 'approved', ?, ?, ?, ?, ?, ?, ?, ?)");
	$seedCommunities = (int)$pdo -> query("SELECT COUNT(*) FROM community_table") -> fetchColumn() === 0;
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

} catch (PDOException|RuntimeException $e) {
	error_log($e -> getMessage());
	http_response_code(500);
	exit("Database setup failed.");
}
?>
