<?php
$host = "localhost";
$dbName = "cos30020a2";
$dbUser = "root";
$dbPass = "";

try {
	$pdo = new PDO(
		"mysql:host=$host;dbname=$dbName;charset=utf8mb4",
		$dbUser,
		$dbPass,
		[
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false
		]
	);
} catch (PDOException $e) {
	error_log($e -> getMessage());
	http_response_code(500);
	exit("Database connection failed.");
}
?>
