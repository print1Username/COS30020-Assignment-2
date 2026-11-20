<?php
session_start();
if ( ($_SESSION["logged_in"] ?? false) !== true ||
	($_SESSION["type"] ?? null) !== "user" || !isset($_SESSION["email"]) ) {
	header("Location: login.php");
	exit;
}

$gameId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
require_once __DIR__."/db_connect.php";
$statement = $pdo -> prepare(
	"SELECT id,name,categories,description,players,duration,image,image_data IS NOT NULL AS has_image_data FROM catalog_info WHERE id=?"
);
$statement -> execute([ $gameId ?: 0 ]);
$game = $statement -> fetch() ?: null;
if ( $game ) $game["categories"] = json_decode($game["categories"], true) ?? [];
if ( $game === null ) {
	http_response_code(404);
}
function catalogDetailEscape($value)
{
	return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

?>
<!DOCTYPE html>
<html lang = "en">
	<head >
		<meta charset = "utf-8">
		<meta
			name = "viewport"
			content = "width=device-width, initial-scale=1"
		>
		<title ><?php echo catalogDetailEscape($game["name"] ?? "Game Not Found"); ?> | Boardgame Hub</title >
		<link
			rel = "icon"
			href = "img/logo/logo.ico"
			sizes = "any"
		>
		<link
			href = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel = "stylesheet"
		>
		<link
			rel = "stylesheet"
			href = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		>
		<link
			rel = "stylesheet"
			href = "style/navbar.css"
		>
		<link
			rel = "stylesheet"
			href = "style/catalog.css"
		>
		<link
			rel = "stylesheet"
			href = "style/catalog_detail.css?v=<?php echo filemtime(__DIR__."/style/catalog_detail.css"); ?>"
		>
	</head >
	<body >
	<?php include "navbar.php"; ?>
	<main class = "catalog-detail-container">
		<a
			class = "catalog-back-link"
			href = "catalog.php"
		>
			<i class = "bi bi-arrow-left"></i >
			Back to Catalogue
		</a >
		<?php if ( $game === null ): ?>
			<section class = "catalog-detail-panel">
				<h1 >Game Not Found</h1 >
				<p >The requested board game is not available. Return to the catalogue to explore other games.</p >
			</section >
		<?php else: ?>
			<header class = "catalog-detail-header">
				<span class = "catalog-detail-eyebrow">BOARDGAME COLLECTION</span >
				<h1 ><?php echo catalogDetailEscape($game["name"]); ?></h1 >
				<div class = "catalog-game-categories">
					<?php foreach ( $game["categories"] ?? [] as $category ): ?>
						<span class = "catalog-category-tag"><?php echo catalogDetailEscape($category); ?></span >
					<?php endforeach; ?>
				</div >
			</header >
			<div class = "catalog-detail-layout">
				<section class = "catalog-detail-panel">
					<img
						class = "catalog-detail-image"
						src = "<?php echo catalogDetailEscape(
							$game["has_image_data"] ? "catalog_image.php?id=".(int)$game["id"] : ($game["image"] ?? "img/boardgames/placeholder.svg")
						); ?>"
						alt = "<?php echo catalogDetailEscape($game["name"]); ?>"
					>
					<div class = "catalog-detail-description">
						<h2 >About the Game</h2 >
						<p ><?php echo catalogDetailEscape($game["description"] ?? ""); ?></p >
					</div >
				</section >
				<aside
					class = "catalog-detail-panel catalog-reservation"
					aria-label = "Game information"
				>
					<span class = "catalog-detail-eyebrow">PLAN YOUR GAME</span >
					<h2 >At a Glance</h2 >
					<dl >
						<dt >
							<i class = "bi bi-people"></i >
							Players
						</dt >
						<dd ><?php echo catalogDetailEscape($game["players"] ?? "Not specified"); ?></dd >
						<dt >
							<i class = "bi bi-clock"></i >
							Playing Time
						</dt >
						<dd ><?php echo catalogDetailEscape($game["duration"] ?? "Not specified"); ?></dd >
						<dt >
							<i class = "bi bi-grid"></i >
							Categories
						</dt >
						<dd ><?php echo catalogDetailEscape(implode(", ", $game["categories"] ?? [])); ?></dd >
					</dl >
				</aside >
			</div >
		<?php endif; ?>
	</main >
	<script src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script >
	</body >
</html >
