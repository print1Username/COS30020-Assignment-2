<?php
require_once __DIR__."/admin_db.php";

$postId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($postId === false || $postId === null) {
	http_response_code(404);
	exit;
}

$statement = $pdo -> prepare("SELECT community_image_data, community_image_mime FROM community_table WHERE id=?");
$statement -> execute([$postId]);
$image = $statement -> fetch();
if (!$image || empty($image["community_image_data"]) ||
	!in_array($image["community_image_mime"], ["image/jpeg", "image/png"], true)) {
	http_response_code(404);
	exit;
}

header("Content-Type: ".$image["community_image_mime"]);
header("Content-Length: ".strlen($image["community_image_data"]));
header("Cache-Control: private, no-store, max-age=0");
header("X-Content-Type-Options: nosniff");
echo $image["community_image_data"];
