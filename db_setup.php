<?php
if ( !defined("DB_INITIALIZE_DUMMY_DATA") ) {
	http_response_code(403);
	exit("Database initialization is not available here.");
}
require_once __DIR__."/upload_helpers.php";
try {
	$catalogFile = __DIR__."/data/catalog.json";
	$catalogSeeds = json_decode(file_get_contents($catalogFile), true);
	if ( !is_array($catalogSeeds) ) throw new RuntimeException("Catalogue seed data is invalid.");
	$insertCatalog = $pdo -> prepare(
		"INSERT INTO catalog_info (id,name,categories,description,players,duration,image,image_data,image_mime) VALUES (?,?,?,?,?,?,?,?,?)"
	);
	foreach ( $catalogSeeds as $catalogSeed ) {
		$insertCatalog -> execute(
			[ $catalogSeed["id"], $catalogSeed["name"], json_encode(
				$catalogSeed["categories"]
			), $catalogSeed["description"], $catalogSeed["players"], $catalogSeed["duration"], $catalogSeed["image"], null, null ]
		);
	}
	$catalogImages = $pdo -> query("SELECT id,image FROM catalog_info WHERE image_data IS NULL AND image IS NOT NULL")
		-> fetchAll(PDO::FETCH_ASSOC);
	$saveCatalogImage = $pdo -> prepare(
		"UPDATE catalog_info SET image_data=?,image_mime=? WHERE id=? AND image_data IS NULL"
	);
	foreach ( $catalogImages as $catalogImage ) {
		$imagePath = $catalogImage["image"];
		if ( !preg_match('~^img/[a-zA-Z0-9_./-]+$~', $imagePath) || str_contains($imagePath, "..") ) continue;
		$absoluteImagePath = __DIR__."/".$imagePath;
		if ( !is_file($absoluteImagePath) ) continue;
		$imageMime = (new finfo(FILEINFO_MIME_TYPE)) -> file($absoluteImagePath);
		if ( !in_array($imageMime, [ "image/jpeg", "image/png", "image/webp", "image/svg+xml" ], true) ) continue;
		$imageData = file_get_contents($absoluteImagePath);
		if ( $imageData !== false ) $saveCatalogImage -> execute([ $imageData, $imageMime, $catalogImage["id"] ]);
	}

	// Homepage slideshow images
	$insertHomepageImage = $pdo -> prepare(
		"INSERT IGNORE INTO homepage_image_table (filename, mime_type, image_data) VALUES (?, ?, ?)"
	);
	for ( $imageNumber = 1; $imageNumber <= 5; $imageNumber++ ) {
		$imagePath = __DIR__."/img/boardgame-".$imageNumber.".jpg";
		if ( is_file($imagePath) ) {
			$insertHomepageImage -> execute(
				[ "boardgame-".$imageNumber.".jpg", "image/jpeg", file_get_contents($imagePath) ]
			);
		}
	}

	$accounts = json_decode(file_get_contents(__DIR__."/data/accounts.json"), true);
	$insertDefaultUser = $pdo -> prepare(
		"INSERT IGNORE INTO user_table (email, first_name, last_name, dob, gender, hometown) VALUES (?, ?, ?, ?, ?, ?)"
	);
	$insertDefaultAccount = $pdo -> prepare(
		"INSERT IGNORE INTO account_table (email, password, type) VALUES (?, ?, ?)"
	);
	$seedAccounts = (int)$pdo -> query("SELECT COUNT(*) FROM account_table") -> fetchColumn() === 0;
	foreach ( $seedAccounts ? $accounts : [] as $account ) {
		$insertDefaultUser -> execute(
			[ $account["email"], $account["first_name"], $account["last_name"], $account["dob"], $account["gender"], $account["hometown"] ]
		);
		$insertDefaultAccount -> execute(
			[ $account["email"], password_hash(
				$account["password"],
				PASSWORD_DEFAULT
			), $account["type"] ]
		);
	}

	$sampleGames = [ [ "Catan", "Strategy", "img/boardgame-1.jpg", "smart_feature_description/sample-catan.pdf" ], [ "Ticket to Ride", "Family Games", "img/boardgame-2.jpg", "smart_feature_description/sample-ticket-to-ride.pdf" ], [ "Carcassonne", "Strategy", "img/boardgame-3.jpg", "smart_feature_description/sample-carcassonne.pdf" ], [ "7 Wonders", "Card Games", "img/boardgame-4.jpg", "smart_feature_description/sample-7-wonders.pdf" ] ];
	$insertSampleGame = $pdo -> prepare(
		"INSERT INTO smart_feature_table (item_name, item_category, item_image, description_file, status)
		SELECT ?, ?, ?, ?, 'approved' WHERE NOT EXISTS
		(SELECT 1 FROM smart_feature_table WHERE item_name = ?)"
	);
	foreach ( $sampleGames as $game ) {
		$insertSampleGame -> execute([ ...$game, $game[0] ]);
	}

	$defaultProfileImages = [ "admin@swin.edu.my" => "admin.png", "user1@swin.edu.my" => "user1.png", "user2@swin.edu.my" => "user2.png" ];
	$setDefaultProfileImage = $pdo -> prepare(
		"UPDATE user_table SET profile_image = ? WHERE email = ? AND (profile_image IS NULL OR profile_image = '' OR profile_image = ?)"
	);
	$defaultImageExists = $pdo -> prepare(
		"SELECT COUNT(*) FROM user_table WHERE email = ? AND profile_image_data IS NOT NULL AND OCTET_LENGTH(profile_image_data) > 0"
	);
	$setDefaultImageData = $pdo -> prepare(
		"UPDATE user_table SET profile_image_data = ?, profile_image_mime = 'image/png' WHERE email = ? AND (profile_image_data IS NULL OR OCTET_LENGTH(profile_image_data) = 0)"
	);
	foreach ( $defaultProfileImages as $seedEmail => $filename ) {
		$relativeImagePath = "profile_images/".$seedEmail."/".$filename;
		$setDefaultProfileImage -> execute([ $relativeImagePath, $seedEmail, "profile_images/".$filename ]);
		$defaultImageExists -> execute([ $seedEmail ]);
		if ( $defaultImageExists -> fetchColumn() ) {
			continue;
		}
		$imagePath = __DIR__."/".$relativeImagePath;
		if ( !is_file($imagePath) || (new finfo(FILEINFO_MIME_TYPE)) -> file($imagePath) !== "image/png" ) {
			throw new RuntimeException("Default profile image is missing or invalid: ".$filename);
		}
		$defaultImage = file_get_contents($imagePath);
		$setDefaultImageData -> execute([ $defaultImage, $seedEmail ]);
	}

	$activities = json_decode(file_get_contents(__DIR__."/data/activities.json"), true);
	$insertActivity = $pdo -> prepare(
		"INSERT IGNORE INTO activity_info (id, date, time, status, title, activity_type, description, end_time, price, venue, table_name, image) VALUES (?, ?, ?, 'approved', ?, ?, ?, ?, ?, ?, ?, ?)"
	);
	$seedActivities = (int)$pdo -> query("SELECT COUNT(*) FROM activity_info") -> fetchColumn() === 0;
	foreach ( $seedActivities ? $activities : [] as $seedActivity ) {
		$insertActivity -> execute(
			[ $seedActivity["id"], $seedActivity["date"], $seedActivity["start_time"], $seedActivity["title"], $seedActivity["type"], $seedActivity["description"], $seedActivity["end_time"], $seedActivity["price"], $seedActivity["venue"], $seedActivity["table"], $seedActivity["image"] ]
		);
	}

	$communities = json_decode(file_get_contents(__DIR__."/data/community.json"), true);
	$insertCommunityInfo = $pdo -> prepare(
		"INSERT IGNORE INTO community_info (id, title, description, member_count, created_at) VALUES (?, ?, ?, ?, ?)"
	);
	$findCommunityPostById = $pdo -> prepare("SELECT id FROM community_table WHERE id = ?");
	$findSeedCommunityPost = $pdo -> prepare(
		"SELECT id FROM community_table WHERE community_id = ? AND title = ? AND first_name = ? AND last_name = ? LIMIT 1"
	);
	$insertCommunityPost = $pdo -> prepare(
		"INSERT INTO community_table (id, community_id, first_name, last_name, contribution_image, community_image_data, community_image_mime, related_activity, status, title, description, created_at)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved', ?, ?, ?)"
	);
	$updateSeedCommunityPost = $pdo -> prepare(
		"UPDATE community_table SET id = ?, community_id = ?, first_name = ?, last_name = ?, contribution_image = ?, community_image_data = ?, community_image_mime = ?, related_activity = ?, status = 'approved', title = ?, description = ?, created_at = ? WHERE id = ?"
	);
	$seedCommunityPostIds = [];
	foreach ( $communities as $seedCommunity ) {
		$insertCommunityInfo -> execute(
			[ $seedCommunity["id"], $seedCommunity["title"], $seedCommunity["description"], $seedCommunity["member_count"], $seedCommunity["created_at"] ]
		);
		$contribution = $seedCommunity["contributions"][0] ?? [];
		$name = explode(" ", $contribution["contributor"] ?? "", 2);
		if ( isset($contribution["id"]) ) {
			$postId = (int)$contribution["id"];
			$seedCommunityPostIds[] = $postId;
			$postTitle = $contribution["title"] ?? null;
			$firstName = $name[0] ?? "";
			$lastName = $name[1] ?? "";
			$imagePath = trim($contribution["media"] ?? "") ?: null;
			$imageData = null;
			$imageMime = null;
			if ( $imagePath !== null ) {
				$absoluteImagePath = __DIR__."/".$imagePath;
				if ( !is_file($absoluteImagePath) || getimagesize($absoluteImagePath) === false ) {
					throw new RuntimeException("Community seed image is invalid: ".$imagePath);
				}
				$imageData = file_get_contents($absoluteImagePath);
				$imageMime = (new finfo(FILEINFO_MIME_TYPE)) -> file($absoluteImagePath);
				if ( $imageData === false || !in_array($imageMime, [ "image/jpeg", "image/png" ], true) ) {
					throw new RuntimeException("Community seed image could not be loaded: ".$imagePath);
				}
			}
			$postValues = [ $postId, $seedCommunity["id"], $firstName, $lastName, $imagePath, $imageData, $imageMime, $contribution["activity"] ?? "", $postTitle, $contribution["description"] ?? null, $contribution["created_at"] ?? null ];

			$findCommunityPostById -> execute([ $postId ]);
			$existingPostId = $findCommunityPostById -> fetchColumn();
			if ( $existingPostId === false ) {
				$findSeedCommunityPost -> execute([ $seedCommunity["id"], $postTitle, $firstName, $lastName ]);
				$existingPostId = $findSeedCommunityPost -> fetchColumn();
			}

			if ( $existingPostId === false ) {
				$insertCommunityPost -> execute($postValues);
			} else {
				$updateSeedCommunityPost -> execute([ ...$postValues, $existingPostId ]);
			}
		}
	}
	$seedPostPlaceholders = implode(", ", array_fill(0, count($seedCommunityPostIds), "?"));
	$deleteRemovedSeedPosts = $pdo -> prepare(
		"DELETE FROM community_table WHERE id BETWEEN 101 AND 108".
		($seedPostPlaceholders ? " AND id NOT IN ($seedPostPlaceholders)" : "")
	);
	$deleteRemovedSeedPosts -> execute($seedCommunityPostIds);

} catch ( PDOException|RuntimeException $e ) {
	error_log($e -> getMessage());
	http_response_code(500);
	exit("Database setup failed.");
}
?>
