<?php
session_start();

if (($_SESSION["logged_in"] ?? false) !== true ||
	($_SESSION["type"] ?? null) !== "user" ||
	!isset($_SESSION["email"])) {
	header("Location: login.php");
	exit;
}

$search = is_string($_GET["search"] ?? null) ? trim($_GET["search"]) : "";
$search = substr($search, 0, 50);
$result = null;

if ($search !== "") {
	require_once __DIR__."/db_connect.php";
	$statement = $pdo->prepare("SELECT item_name, item_category, item_image, description_file
		FROM smart_feature_table
		WHERE status = 'approved'
		AND (LOCATE(?, item_name) > 0 OR LOCATE(?, item_category) > 0)
		ORDER BY CASE WHEN item_name = ? THEN 0 ELSE 1 END, item_name
		LIMIT 1");
	$statement->execute([$search, $search, $search]);
	$result = $statement->fetch() ?: null;
}

function smartFeatureEscape($value): string
{
	return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function smartFeatureAsset(?string $path): ?string
{
	if ($path === null || !preg_match('~^(?:img|smart_feature_images|smart_feature_description)/[a-zA-Z0-9._/-]+$~', $path) ||
		str_contains($path, "..") || !is_file(__DIR__."/".$path)) {
		return null;
	}
	return $path;
}
?>
