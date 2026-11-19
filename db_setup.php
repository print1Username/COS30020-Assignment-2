<?php
$host = "localhost";
$dbName = "cos30020a2";
$dbUser = "root";
$dbPass = "";

try {
	// Connect to MySQL server
	$pdo = new PDO("mysql:host=$host;charset=utf8mb4", $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

	// Create database automatically
	$pdo -> exec("
        CREATE DATABASE IF NOT EXISTS `$dbName`
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci
    ");

	// Select database
	$pdo -> exec("USE `$dbName`");
	require_once __DIR__."/upload_helpers.php";
	$schemaFiles = ["homepage_image_table.sql", "user_table.sql", "account_table.sql", "smart_feature_table.sql", "activity_info.sql", "activity_table.sql", "community_info.sql", "community_table.sql", "community_member.sql"];
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
			$insertHomepageImage -> execute(["boardgame-".$imageNumber.".jpg", "image/jpeg", file_get_contents($imagePath)]);
		}
	}

	$accounts = json_decode(file_get_contents(__DIR__."/data/accounts.json"), true);
	$insertDefaultUser = $pdo -> prepare("INSERT IGNORE INTO user_table (email, first_name, last_name, dob, gender, hometown) VALUES (?, ?, ?, ?, ?, ?)");
	$insertDefaultAccount = $pdo -> prepare("INSERT IGNORE INTO account_table (email, password, type) VALUES (?, ?, ?)");
	$seedAccounts = (int)$pdo -> query("SELECT COUNT(*) FROM account_table") -> fetchColumn() === 0;
	foreach ($seedAccounts ? $accounts : [] as $account) {
		$insertDefaultUser -> execute([$account["email"], $account["first_name"], $account["last_name"], $account["dob"], $account["gender"], $account["hometown"]]);
		$insertDefaultAccount -> execute([$account["email"], password_hash($account["password"],
			PASSWORD_DEFAULT), $account["type"]]);
	}

	$sampleGames = [["Catan", "Strategy", "img/boardgame-1.jpg", "smart_feature_description/sample-catan.pdf"], ["Ticket to Ride", "Family Games", "img/boardgame-2.jpg", "smart_feature_description/sample-ticket-to-ride.pdf"], ["Carcassonne", "Strategy", "img/boardgame-3.jpg", "smart_feature_description/sample-carcassonne.pdf"], ["7 Wonders", "Card Games", "img/boardgame-4.jpg", "smart_feature_description/sample-7-wonders.pdf"]];
	$insertSampleGame = $pdo -> prepare("INSERT INTO smart_feature_table (item_name, item_category, item_image, description_file, status)
		SELECT ?, ?, ?, ?, 'approved' WHERE NOT EXISTS
		(SELECT 1 FROM smart_feature_table WHERE item_name = ?)");
	foreach ($sampleGames as $game) {
		$insertSampleGame -> execute([...$game, $game[0]]);
	}

	// Add Part 3 columns to databases created by the earlier version.
	$requiredColumns = [
		"user_table" => ["resume_file" => "VARCHAR(100) NULL", "resume_filename" => "VARCHAR(255) NULL", "profile_image" => "VARCHAR(255) NULL", "profile_image_data" => "LONGBLOB NULL", "profile_image_mime" => "VARCHAR(30) NULL"],
		"community_table" => ["email" => "VARCHAR(50) NULL"]
	];
	$columnCheck = $pdo -> prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?");
	foreach ($requiredColumns as $table => $columns) {
		foreach ($columns as $column => $definition) {
			$columnCheck -> execute([$dbName, $table, $column]);
			if (!$columnCheck -> fetchColumn()) {
				$pdo -> exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
			}
		}
	}
	$pdo -> exec("ALTER TABLE user_table MODIFY profile_image VARCHAR(255) NULL");

	$defaultProfileImages = ["admin@swin.edu.my" => "admin.png", "user1@swin.edu.my" => "user1.png", "user2@swin.edu.my" => "user2.png"];
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
		$insertActivity -> execute([$seedActivity["id"], $seedActivity["date"], $seedActivity["start_time"], $seedActivity["title"], $seedActivity["type"], $seedActivity["description"], $seedActivity["end_time"], $seedActivity["price"], $seedActivity["venue"], $seedActivity["table"], $seedActivity["image"]]);
	}

	$tableCheck = $pdo -> prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
	$tableCheck -> execute([$dbName, "community_table_legacy"]);
	$legacyCommunityExists = (bool)$tableCheck -> fetchColumn();
	$columnCheck -> execute([$dbName, "community_table", "record_type"]);
	$combinedCommunityExists = (bool)$columnCheck -> fetchColumn();
	if ($combinedCommunityExists && !$legacyCommunityExists) {
		$pdo -> exec("RENAME TABLE community_table TO community_table_legacy");
		$legacyCommunityExists = true;
		$sql = file_get_contents(__DIR__."/database/community_table.sql");
		if ($sql === false) {
			throw new RuntimeException("Database schema file could not be read: community_table.sql");
		}
		$pdo -> exec($sql);
	}
	if ($legacyCommunityExists) {
		$pdo -> beginTransaction();
		try {
			$pdo -> exec("INSERT IGNORE INTO community_info (id, title, description, member_count, created_at)
				SELECT id, COALESCE(title, related_activity), description, COALESCE(member_count, 0), created_at
				FROM community_table_legacy WHERE record_type = 'community'");
			$pdo -> exec("INSERT IGNORE INTO community_table (id, community_id, first_name, last_name, contribution_image, related_activity, status, title, description, created_at)
				SELECT id, id, first_name, last_name, contribution_image, related_activity, status, contribution_title, contribution_description, contribution_date
				FROM community_table_legacy WHERE record_type = 'community'");
			$pdo -> exec("INSERT IGNORE INTO community_member (id, community_id, email, status)
				SELECT id, community_id, email, status FROM community_table_legacy WHERE record_type = 'join'");
			$pdo -> commit();
		} catch (Throwable $e) {
			$pdo -> rollBack();
			throw $e;
		}
		$pdo -> exec("DROP TABLE community_table_legacy");
	}
	$communityPostUserForeignKey = $pdo -> prepare("SELECT COUNT(*) FROM information_schema.referential_constraints WHERE constraint_schema = ? AND table_name = 'community_table' AND constraint_name = 'fk_community_post_user'");
	$communityPostUserForeignKey -> execute([$dbName]);
	if (!$communityPostUserForeignKey -> fetchColumn()) {
		$pdo -> exec("ALTER TABLE community_table ADD CONSTRAINT fk_community_post_user FOREIGN KEY (email) REFERENCES user_table(email) ON UPDATE CASCADE ON DELETE SET NULL");
	}

	$communities = json_decode(file_get_contents(__DIR__."/data/community.json"), true);
	$insertCommunityInfo = $pdo -> prepare("INSERT IGNORE INTO community_info (id, title, description, member_count, created_at) VALUES (?, ?, ?, ?, ?)");
	$insertCommunityPost = $pdo -> prepare("INSERT INTO community_table (id, community_id, first_name, last_name, contribution_image, related_activity, status, title, description, created_at)
		SELECT ?, ?, ?, ?, ?, ?, 'approved', ?, ?, ? WHERE NOT EXISTS
		(SELECT 1 FROM community_table WHERE community_id = ? AND title = ? AND first_name = ? AND last_name = ?)");
	foreach ($communities as $seedCommunity) {
		$insertCommunityInfo -> execute([$seedCommunity["id"], $seedCommunity["title"], $seedCommunity["description"], $seedCommunity["member_count"], $seedCommunity["created_at"]]);
		$contribution = $seedCommunity["contributions"][0] ?? [];
		$name = explode(" ", $contribution["contributor"] ?? "", 2);
		if (isset($contribution["id"])) {
			$insertCommunityPost -> execute([$contribution["id"], $seedCommunity["id"], $name[0] ?? "", $name[1] ?? "", $contribution["media"] ?? null, $contribution["activity"] ?? "", $contribution["title"] ?? null, $contribution["description"] ?? null, $contribution["created_at"] ?? null, $seedCommunity["id"], $contribution["title"] ?? null, $name[0] ?? "", $name[1] ?? ""]);
		}
	}

} catch (PDOException|RuntimeException $e) {
	error_log($e -> getMessage());
	http_response_code(500);
	exit("Database setup failed.");
}
?>
