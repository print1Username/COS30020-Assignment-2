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
$statement = $pdo -> prepare("SELECT profile_image FROM user_table WHERE email = ?");
$statement -> execute([$_SESSION["email"]]);
$imagePath = $statement -> fetchColumn();
if (!profileImageExists($_SESSION["email"], $imagePath)) {
	http_response_code(404);
	exit;
}

$mime = (new finfo(FILEINFO_MIME_TYPE)) -> file(__DIR__."/".$imagePath);
if (!in_array($mime, ["image/jpeg", "image/png"], true)) {
	http_response_code(404);
	exit;
}
header("Content-Type: ".$mime);
header("Content-Length: ".filesize(__DIR__."/".$imagePath));
header("X-Content-Type-Options: nosniff");
readfile(__DIR__."/".$imagePath);
