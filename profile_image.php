<?php
session_start();

if (($_SESSION["logged_in"] ?? false) !== true ||
	($_SESSION["type"] ?? null) !== "user" ||
	!isset($_SESSION["email"])) {
	http_response_code(403);
	exit;
}

require_once __DIR__ . "/db_setup.php";
require_once __DIR__ . "/db_connect.php";
$statement = $pdo->prepare("SELECT profile_image_data, profile_image_mime FROM user_table WHERE email = ?");
$statement->execute([$_SESSION["email"]]);
$image = $statement->fetch();
if (!$image || empty($image["profile_image_data"]) ||
	!in_array($image["profile_image_mime"], ["image/jpeg", "image/png"], true)) {
	http_response_code(404);
	exit;
}

header("Content-Type: " . $image["profile_image_mime"]);
header("X-Content-Type-Options: nosniff");
echo $image["profile_image_data"];
