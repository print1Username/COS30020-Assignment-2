<?php
session_start();

// Load boardgame data from JSON
$catalogueFile = "data/catalog.json";
$games = [];

if (file_exists($catalogueFile)) {
	$jsonData = file_get_contents($catalogueFile);
	$decodedData = json_decode($jsonData, true);

	if (is_array($decodedData)) {
		$games = $decodedData;
	}
}

// Escape output safely
function escapeHTML($value)
{
	return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Available categories
$categories = [
	"Strategy",
	"Party Games",
	"Family Games",
	"Card Games",
	"Cooperative"
];
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Catalogue | Boardgame Hub</title>

		<!-- Bootstrap 5.3.8 -->
		<link
			href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel="stylesheet"
			integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
			crossorigin="anonymous"
		>

		<!-- Bootstrap Icons -->
		<link
			rel="stylesheet"
			href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		>

		<!-- Custom Navbar CSS -->
		<link rel="stylesheet" href="style/navbar.css">

		<!-- Catalogue CSS -->
		<link rel="stylesheet" href="style/catalog.css">
		<link rel="stylesheet" href="style/pagination.css">

	</head>
	<body>

	<!-- Navigation -->
	<?php include "navbar.php"; ?>

	<main>

		<!-- Catalogue Header -->
		<section class="catalog-header">
			<div class="container">
				<div class="catalog-header-content">
                    <span class="catalog-eyebrow">
                        <i class="bi bi-dice-5"></i>
                        Explore Our Collection
                    </span>

					<h1>Boardgame Catalogue</h1>

					<p>
						Discover your next favourite boardgame.
						From strategic adventures to fun-filled party games,
						there is something for everyone.
					</p>
				</div>
			</div>
		</section>

		<!-- Catalogue Content -->
		<section class="catalog-section">
			<div class="container">

				<!-- Section Heading -->
				<div class="catalog-section-heading">
					<div>
						<h2>Explore Boardgames</h2>
						<p>
							Browse our collection and find
							the perfect game for your next session.
						</p>
					</div>

					<div class="catalog-total">
						<i class="bi bi-collection"></i>
						<span id="catalog-game-count">
                            <?php echo count($games); ?> Games
                        </span>
					</div>
				</div>

				<!-- Category Filters -->
				<div
					class="catalog-filter-wrapper"
					role="group"
					aria-label="Filter boardgames by category"
				>
					<button
						type="button"
						class="catalog-filter-btn active"
						data-category="all"
						aria-pressed="true"
					>
						All Games
					</button>

					<?php foreach ($categories as $category): ?>
						<button
							type="button"
							class="catalog-filter-btn"
							data-category="<?php echo escapeHTML($category); ?>"
							aria-pressed="false"
						>
							<?php echo escapeHTML($category); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<!-- Game Grid -->
				<div
					class="row g-4 catalog-game-grid"
					id="catalog-game-grid"
				>
					<?php foreach ($games as $game): ?>

						<?php
						$gameCategories = $game["categories"] ?? [];

						/*
						 * Use | as the separator because some category
						 * names contain spaces, such as "Party Games".
						 */
						$categoryAttributes = implode("|", $gameCategories);
						?>

						<div
							class="col-12 col-sm-6 col-lg-4 col-xl-3 catalog-game-item"
							data-categories="<?php echo escapeHTML($categoryAttributes); ?>"
						>
							<article class="catalog-game-card">

								<!-- Game Image -->
								<div class="catalog-game-image-wrapper">
									<img
										src="<?php echo escapeHTML($game["image"] ?? "img/boardgames/placeholder.svg"); ?>"
										alt="<?php echo escapeHTML($game["name"] ?? "Boardgame"); ?>"
										class="catalog-game-image"
										loading="lazy"
									>

									<span class="catalog-image-overlay">
                                        <i class="bi bi-dice-5"></i>
                                    </span>
								</div>

								<!-- Game Information -->
								<div class="catalog-game-body">

									<h3 class="catalog-game-title">
										<?php echo escapeHTML($game["name"] ?? "Untitled Game"); ?>
									</h3>

									<!-- Categories -->
									<div class="catalog-game-categories">
										<?php foreach ($gameCategories as $gameCategory): ?>
											<span class="catalog-category-tag">
                                                <?php echo escapeHTML($gameCategory); ?>
                                            </span>
										<?php endforeach; ?>
									</div>

									<!-- Description -->
									<p class="catalog-game-description">
										<?php echo escapeHTML($game["description"] ?? "Game description coming soon."); ?>
									</p>

									<!-- Game Details -->
									<div class="catalog-game-details">
										<div class="catalog-detail-item">
											<i class="bi bi-people"></i>
											<span>
                                                <?php echo escapeHTML($game["players"] ?? "N/A"); ?>
                                            </span>
										</div>

										<div class="catalog-detail-item">
											<i class="bi bi-clock"></i>
											<span>
                                                <?php echo escapeHTML($game["duration"] ?? "N/A"); ?>
                                            </span>
										</div>
									</div>

									<!-- Reservation Button -->
									<a
										href="#"
										class="catalog-reserve-btn"
										aria-label="Reserve <?php echo escapeHTML($game["name"] ?? "boardgame"); ?>"
									>
										<i class="bi bi-calendar-check"></i>
										Reserve Now
									</a>

								</div>
							</article>
						</div>

					<?php endforeach; ?>
				</div>

				<!-- Pagination -->
				<nav
					class="catalog-pagination"
					aria-label="Boardgame pagination"
				>
					<ul
						class="pagination justify-content-center"
						id="catalog-pagination"
					></ul>
				</nav>

				<!-- Empty State -->
				<div
					class="catalog-empty-state"
					id="catalog-empty-state"
					hidden
				>
					<i class="bi bi-search"></i>

					<h3>No Games Found</h3>

					<p>
						There are currently no boardgames
						available in this category.
					</p>
				</div>

			</div>
		</section>

	</main>

	<!-- Bootstrap JavaScript -->
	<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
		crossorigin="anonymous"
	></script>

	<!-- Pagination Component -->
	<script src="components/pagination.js"></script>

	<!-- Catalogue JavaScript -->
	<script src="components/catalog.js"></script>

	</body>
</html>