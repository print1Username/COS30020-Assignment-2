<?php
session_start();

if (($_SESSION["logged_in"] ?? false) !== true ||
	!in_array($_SESSION["type"] ?? null, ["user", "admin"], true) ||
	!isset($_SESSION["email"])) {
	http_response_code(403);
	exit;
}

require_once __DIR__ . "/db_setup.php";
require_once __DIR__ . "/db_connect.php";
$statement = $pdo->prepare("SELECT OCTET_LENGTH(profile_image_data) AS image_size, profile_image_mime FROM user_table WHERE email = ?");
$statement->execute([$_SESSION["email"]]);
$image = $statement->fetch();
if (!$image || empty($image["image_size"]) ||
	!in_array($image["profile_image_mime"], ["image/jpeg", "image/png"], true)) {
	http_response_code(404);
	exit;
}

header("Content-Type: " . $image["profile_image_mime"]);
header("Content-Length: " . $image["image_size"]);
header("X-Content-Type-Options: nosniff");
$readChunk = $pdo->prepare("SELECT SUBSTRING(profile_image_data, ?, 131072) FROM user_table WHERE email = ?");
for ($offset = 1; $offset <= $image["image_size"]; $offset += 131072) {
	$readChunk->execute([$offset, $_SESSION["email"]]);
	echo $readChunk->fetchColumn();
}
