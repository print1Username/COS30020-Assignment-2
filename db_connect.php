<?php
$host = "localhost";
$dbName = "cos30020a2";
$dbUser = "root";
$dbPass = "";

try {
	$pdo = new PDO(
		"mysql:host=$host;charset=utf8mb4", $dbUser, $dbPass, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false
		]
	);
	$databaseExists = $pdo -> query(
			"SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'cos30020a2'"
		)
			-> fetchColumn() !== false;
	$pdo -> exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
	$pdo -> exec("USE `$dbName`");
	$schemaFiles = [ "homepage_image_table.sql", "user_table.sql", "account_table.sql", "catalog_info.sql", "smart_feature_table.sql", "activity_info.sql", "activity_table.sql", "community_info.sql", "community_table.sql", "community_member.sql" ];
	foreach ( $schemaFiles as $schemaFile ) {
		$sql = file_get_contents(__DIR__."/database/".$schemaFile);
		if ( $sql === false ) throw new RuntimeException("Database schema file could not be read: ".$schemaFile);
		$pdo -> exec($sql);
	}
	$catalogColumns = $pdo -> query("SHOW COLUMNS FROM catalog_info LIKE 'status'") -> fetchColumn();
	if ( $catalogColumns === false ) {
		$pdo -> exec("ALTER TABLE catalog_info ADD COLUMN status VARCHAR(10) NOT NULL DEFAULT 'approved'");
	}
	$smartColumns = $pdo -> query("SHOW COLUMNS FROM smart_feature_table") -> fetchAll(PDO::FETCH_COLUMN);
	if ( !in_array("catalog_id", $smartColumns, true) ) {
		$pdo -> exec("ALTER TABLE smart_feature_table ADD COLUMN catalog_id INT NULL UNIQUE");
	}
	if ( !in_array("item_description", $smartColumns, true) ) {
		$pdo -> exec("ALTER TABLE smart_feature_table ADD COLUMN item_description TEXT NULL");
	}
	if ( !in_array("players", $smartColumns, true) ) {
		$pdo -> exec("ALTER TABLE smart_feature_table ADD COLUMN players VARCHAR(50) NULL");
	}
	if ( !in_array("duration", $smartColumns, true) ) {
		$pdo -> exec("ALTER TABLE smart_feature_table ADD COLUMN duration VARCHAR(50) NULL");
	}
	$smartForeignKeys = $pdo -> query(
		"SELECT constraint_name FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'smart_feature_table' AND constraint_type = 'FOREIGN KEY'"
	) -> fetchAll(PDO::FETCH_COLUMN);
	if ( !in_array("fk_smart_feature_catalog", $smartForeignKeys, true) ) {
		$pdo -> exec("ALTER TABLE smart_feature_table ADD CONSTRAINT fk_smart_feature_catalog FOREIGN KEY (catalog_id) REFERENCES catalog_info(id) ON DELETE CASCADE");
	}
	require_once __DIR__."/catalog_smart_sync.php";
	synchronizeCatalogSmartFeature($pdo);
	if ( !$databaseExists ) {
		define("DB_INITIALIZE_DUMMY_DATA", true);
		require __DIR__."/db_setup.php";
		synchronizeCatalogSmartFeature($pdo);
	}
} catch ( PDOException|RuntimeException $e ) {
	error_log($e -> getMessage());
	http_response_code(500);
	exit("Database connection failed.");
}
?>
