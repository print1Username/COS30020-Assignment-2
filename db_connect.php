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
	$schemaFiles = [ "homepage_image_table.sql", "user_table.sql", "account_table.sql", "smart_feature_table.sql", "activity_info.sql", "activity_table.sql", "community_info.sql", "community_table.sql", "community_member.sql", "catalog_info.sql" ];
	foreach ( $schemaFiles as $schemaFile ) {
		$sql = file_get_contents(__DIR__."/database/".$schemaFile);
		if ( $sql === false ) throw new RuntimeException("Database schema file could not be read: ".$schemaFile);
		$pdo -> exec($sql);
	}
	if ( !$databaseExists ) {
		define("DB_INITIALIZE_DUMMY_DATA", true);
		require __DIR__."/db_setup.php";
	}
} catch ( PDOException|RuntimeException $e ) {
	error_log($e -> getMessage());
	http_response_code(500);
	exit("Database connection failed.");
}
?>
