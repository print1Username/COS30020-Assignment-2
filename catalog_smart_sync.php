<?php

function synchronizeCatalogSmartFeature(PDO $pdo): void
{
	$catalogues = $pdo -> query(
		"SELECT id,name,categories,description,players,duration,image,status FROM catalog_info ORDER BY id"
	) -> fetchAll();
	$findLinked = $pdo -> prepare("SELECT id FROM smart_feature_table WHERE catalog_id=?");
	$linkSeed = $pdo -> prepare(
		"UPDATE smart_feature_table SET catalog_id=? WHERE catalog_id IS NULL AND item_name=? AND status='approved' LIMIT 1"
	);
	$upsert = $pdo -> prepare(
		"INSERT INTO smart_feature_table (catalog_id,item_name,item_category,item_image,item_description,players,duration,status)
		VALUES (?,?,?,?,?,?,?,?)
		ON DUPLICATE KEY UPDATE item_name=VALUES(item_name),item_category=VALUES(item_category),
		item_image=VALUES(item_image),item_description=VALUES(item_description),players=VALUES(players),
		duration=VALUES(duration),status=VALUES(status)"
	);
	$legacyPdfGames = [ "Catan", "Ticket to Ride", "Carcassonne", "7 Wonders" ];
	foreach ( $catalogues as $catalogue ) {
		$findLinked -> execute([ $catalogue["id"] ]);
		if ( !$findLinked -> fetchColumn() && in_array($catalogue["name"], $legacyPdfGames, true) ) {
			$linkSeed -> execute([ $catalogue["id"], $catalogue["name"] ]);
		}
		$categories = json_decode($catalogue["categories"], true);
		$categories = is_array($categories) ? $categories : [];
		$upsert -> execute([
			$catalogue["id"], $catalogue["name"], implode(", ", $categories), $catalogue["image"],
			$catalogue["description"], $catalogue["players"], $catalogue["duration"], $catalogue["status"]
		]);
	}
}
