<?php
session_start();

if (($_SESSION["logged_in"] ?? false) !== true ||
	!in_array($_SESSION["type"] ?? null, ["user", "admin"], true) ||
	!isset($_SESSION["email"])) {
	http_response_code(403);
	exit;
}

require_once __DIR__."/db_setup.php";
require_once __DIR__."/db_connect.php";
require_once __DIR__."/upload_helpers.php";
$imageEmail = $_SESSION["email"];
if (isset($_GET["email"])) {
	$imageEmail = filter_input(INPUT_GET, "email", FILTER_VALIDATE_EMAIL);
	if ($imageEmail === false || $imageEmail === null) {
		http_response_code(404);
		exit;
	}
}
$statement = $pdo -> prepare("SELECT profile_image_data, profile_image_mime FROM user_table WHERE email = ?");
$statement -> execute([$imageEmail]);
$image = $statement -> fetch();
if (!$image || empty($image["profile_image_data"]) ||
	!in_array($image["profile_image_mime"], ["image/jpeg", "image/png"], true)) {
	http_response_code(404);
	exit;
}
header("Content-Type: ".$image["profile_image_mime"]);
header("Content-Length: ".strlen($image["profile_image_data"]));
header("Cache-Control: no-store, max-age=0");
header("X-Content-Type-Options: nosniff");
echo $image["profile_image_data"];
