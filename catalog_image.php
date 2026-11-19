<?php
require_once __DIR__."/auth.php";
requireUser();
require_once __DIR__."/db_connect.php";

$gameId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$gameId) {
	http_response_code(404);
	exit;
}
$statement = $pdo -> prepare("SELECT image_data,image_mime FROM catalog_info WHERE id=?");
$statement -> execute([$gameId]);
$image = $statement -> fetch();
if (!$image || empty($image["image_data"]) || !in_array($image["image_mime"],
		["image/jpeg", "image/png", "image/webp", "image/svg+xml"], true)) {
	http_response_code(404);
	exit;
}
header("Content-Type: ".$image["image_mime"]);
header("Content-Length: ".strlen($image["image_data"]));
header("X-Content-Type-Options: nosniff");
echo $image["image_data"];
