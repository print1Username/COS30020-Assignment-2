<?php
if ( session_status() !== PHP_SESSION_ACTIVE ) session_start();

if ( ($_SESSION["logged_in"] ?? false) !== true ||
	($_SESSION["type"] ?? null) !== "user" || !isset($_SESSION["email"]) ) {
	header("Location: login.php");
	exit;
}

require_once __DIR__."/db_connect.php";

function smartFeatureEscape($value): string
{
	return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function smartFeatureAsset(?string $path): ?string
{
	if ( $path === null || !preg_match(
			'~^(?:img|smart_feature_images|smart_feature_description)/[a-zA-Z0-9._/-]+$~',
			$path
	) || str_contains($path, "..") || !is_file(__DIR__."/".$path) ) return null;
	return $path;
}

function smartFeatureRange(string $value): ?array
{
	preg_match_all('/[0-9]+/', $value, $matches);
	$numbers = array_map('intval', $matches[0] ?? []);
	if ( !$numbers ) return null;
	return [ $numbers[0], str_contains($value, "+") ? PHP_INT_MAX : ($numbers[1] ?? $numbers[0]) ];
}

function smartFeatureItem(array $row): array
{
	if ( $row["catalog_id"] !== null ) {
		$categories = json_decode($row["catalog_categories"], true);
		$categories = is_array($categories) ? $categories : [];
		$row["item_name"] = $row["catalog_name"];
		$row["item_category"] = implode(", ", $categories);
		$row["item_description"] = $row["catalog_description"];
		$row["players"] = $row["catalog_players"];
		$row["duration"] = $row["catalog_duration"];
		$row["categories"] = $categories;
		$row["image_url"] = $row["has_catalog_image"]
			? "catalog_image.php?id=".(int)$row["catalog_id"]
			: smartFeatureAsset($row["catalog_image"]);
	} else {
		$row["categories"] = [ $row["item_category"] ];
		$row["image_url"] = smartFeatureAsset($row["item_image"]);
	}
	return $row;
}

$rows = $pdo -> query(
	"SELECT feature.id,feature.catalog_id,feature.item_name,feature.item_category,feature.item_image,
		feature.item_description,feature.players,feature.duration,feature.description_file,
		catalog.name AS catalog_name,catalog.categories AS catalog_categories,
		catalog.description AS catalog_description,catalog.players AS catalog_players,
		catalog.duration AS catalog_duration,catalog.image AS catalog_image,
		catalog.image_data IS NOT NULL AS has_catalog_image
	FROM smart_feature_table AS feature
	LEFT JOIN catalog_info AS catalog ON catalog.id=feature.catalog_id
	WHERE feature.status='approved' AND (feature.catalog_id IS NULL OR catalog.status='approved')"
) -> fetchAll();
$items = array_map('smartFeatureItem', $rows);
$search = is_string($_GET["search"] ?? null) ? trim($_GET["search"]) : "";
$search = substr($search, 0, 50);
$result = null;
if ( $search !== "" ) {
	$searchLower = strtolower($search);
	$matches = array_values(array_filter($items, static function (array $item) use ($searchLower): bool {
		return str_contains(strtolower($item["item_name"]), $searchLower) ||
			str_contains(strtolower(implode(" ", $item["categories"])), $searchLower);
	}));
	usort($matches, static fn(array $first, array $second): int =>
		($first["item_name"] === $search ? 0 : 1) <=> ($second["item_name"] === $search ? 0 : 1) ?: strcmp($first["item_name"], $second["item_name"])
	);
	$result = $matches[0] ?? null;
}

$recommendationError = "";
$recommendations = [];
$recommendationPlayers = "";
$recommendationDuration = "";
$selectedPreferences = $_GET["preferences"] ?? [];
$availablePreferences = [ "Strategy", "Party Games", "Family Games", "Card Games", "Cooperative" ];
if ( !is_array($selectedPreferences) ) $selectedPreferences = [];
$selectedPreferences = array_values(array_intersect($availablePreferences, $selectedPreferences));
if ( isset($_GET["recommend"]) ) {
	$recommendationPlayers = trim((string)($_GET["player_count"] ?? ""));
	$recommendationDuration = trim((string)($_GET["max_duration"] ?? ""));
	if ( !preg_match('/^[1-9][0-9]?$/', $recommendationPlayers) ||
		!preg_match('/^[1-9][0-9]{0,2}$/', $recommendationDuration) ) {
		$recommendationError = "Enter a player count from 1 to 99 and a maximum playing time in minutes.";
	} else {
		$playerCount = (int)$recommendationPlayers;
		$maximumDuration = (int)$recommendationDuration;
		foreach ( $items as $item ) {
			if ( $item["catalog_id"] === null ) continue;
			$playerRange = smartFeatureRange($item["players"]);
			$durationRange = smartFeatureRange($item["duration"]);
			if ( $playerRange === null || $durationRange === null || $playerCount < $playerRange[0] ||
				$playerCount > $playerRange[1] || $durationRange[1] > $maximumDuration ||
				($selectedPreferences && !array_intersect($selectedPreferences, $item["categories"])) ) continue;
			$item["preference_score"] = count(array_intersect($selectedPreferences, $item["categories"]));
			$recommendations[] = $item;
		}
		usort($recommendations, static fn(array $first, array $second): int =>
			$second["preference_score"] <=> $first["preference_score"] ?: strcmp($first["item_name"], $second["item_name"])
		);
	}
}
