<?php
session_start();

/*
 * ==========================
 * Community Data
 * ==========================
 */

$dataFile = "data/community.json";
$communities = [];

if (file_exists($dataFile)) {
	$jsonData = file_get_contents($dataFile);
	$decodedData = json_decode($jsonData, true);

	if (is_array($decodedData)) {
		$communities = $decodedData;
	}
}

/*
 * ==========================
 * Sorting Settings
 * ==========================
 */

$sortType = $_GET["sort_type"] ?? "time";
$sortOrder = $_GET["sort_order"] ?? "ascending";

$allowedSortTypes = ["time", "alphabetical"];
$allowedSortOrders = ["ascending", "descending"];

if (!in_array($sortType, $allowedSortTypes, true)) {
	$sortType = "time";
}

if (!in_array($sortOrder, $allowedSortOrders, true)) {
	$sortOrder = "ascending";
}

/*
 * ==========================
 * Sorting Logic
 * ==========================
 */

usort($communities, function($a, $b) use ($sortType, $sortOrder) {
	if ($sortType === "alphabetical") {
		$result = strcasecmp(
			$a["title"] ?? "",
			$b["title"] ?? ""
		);
	} else {
		$result = strcmp(
			$a["created_at"] ?? "",
			$b["created_at"] ?? ""
		);
	}

	return $sortOrder === "descending" ? -$result : $result;
});

/*
 * ==========================
 * Helper Function
 * ==========================
 */

function communityEscape($value)
{
	return htmlspecialchars(
		(string)$value,
		ENT_QUOTES,
		"UTF-8"
	);
}

function communitySelected($current, $value)
{
	return $current === $value ? "checked" : "";
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Community | Boardgame Hub</title>

		<!-- Bootstrap 5.3.8 -->
		<link
			href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel="stylesheet"
			integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
			crossorigin="anonymous"
		>

		<!-- Custom Navbar CSS -->
		<link rel="stylesheet" href="style/navbar.css">

		<!-- Community CSS -->
		<link rel="stylesheet" href="style/community.css">

		<link rel="stylesheet" href="style/pagination.css">


		<!-- Bootstrap Icons -->
		<link
			rel="stylesheet"
			href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		>
	</head>

	<body>

	<!-- Navbar -->
	<?php include "navbar.php"; ?>

	<main class="community-container">

		<!-- ==========================
			 Page Header
		=========================== -->

		<section class="community-header">

			<div class="community-heading">
				<h1>Community</h1>
				<p>
					Connect with fellow boardgame enthusiasts
					and discover communities that share your interests.
				</p>
			</div>

			<a href="#" class="btn community-create-btn">
				<i class="bi bi-plus-lg"></i>
				Create Community
			</a>

		</section>

		<!-- ==========================
			 Sorting Toolbar
		=========================== -->

		<section class="community-toolbar">

			<div class="community-sort-group">

				<!-- Sorting Type -->
				<details class="community-sort-dropdown">

					<summary class="community-sort-trigger">
						<i class="bi bi-funnel"></i>
						<span>Sorting Type</span>
						<i class="bi bi-chevron-down community-sort-chevron"></i>
					</summary>

					<div class="community-sort-options">

						<form method="GET" action="community.php">

							<input
								type="hidden"
								name="sort_order"
								value="<?php echo communityEscape($sortOrder); ?>"
							>

							<label class="community-sort-option">
								<input
									type="radio"
									name="sort_type"
									value="time"
									<?php echo communitySelected($sortType, "time"); ?>
									onchange="this.form.submit()"
								>
								<span>Time</span>
							</label>

							<label class="community-sort-option">
								<input
									type="radio"
									name="sort_type"
									value="alphabetical"
									<?php echo communitySelected($sortType, "alphabetical"); ?>
									onchange="this.form.submit()"
								>
								<span>Alphabetical</span>
							</label>

						</form>

					</div>

				</details>

				<!-- Sorting Order -->
				<details class="community-sort-dropdown">

					<summary class="community-sort-trigger">
						<i class="bi bi-sort-down"></i>
						<span>Sorting Order</span>
						<i class="bi bi-chevron-down community-sort-chevron"></i>
					</summary>

					<div class="community-sort-options">

						<form method="GET" action="community.php">

							<input
								type="hidden"
								name="sort_type"
								value="<?php echo communityEscape($sortType); ?>"
							>

							<label class="community-sort-option">
								<input
									type="radio"
									name="sort_order"
									value="ascending"
									<?php echo communitySelected($sortOrder, "ascending"); ?>
									onchange="this.form.submit()"
								>
								<span>Ascending</span>
							</label>

							<label class="community-sort-option">
								<input
									type="radio"
									name="sort_order"
									value="descending"
									<?php echo communitySelected($sortOrder, "descending"); ?>
									onchange="this.form.submit()"
								>
								<span>Descending</span>
							</label>

						</form>

					</div>

				</details>

			</div>

			<div class="community-result-count">
				<?php echo count($communities); ?> Communities
			</div>

		</section>

		<!-- ==========================
			 Community Cards
		=========================== -->

		<section class="community-grid">

			<?php if (empty($communities)): ?>

				<div class="community-empty">
					<i class="bi bi-people"></i>
					<h3>No Communities Available</h3>
					<p>There are currently no communities to display.</p>
				</div>

			<?php else: ?>

				<?php foreach ($communities as $community): ?>

					<?php
					$communityId = $community["id"] ?? "";
					$communityTitle = $community["title"] ?? "Untitled Community";
					$communityDescription = $community["description"] ?? "No description available.";
					$memberCount = $community["member_count"] ?? 0;
					?>

					<article class="community-card">

						<a
							href="community_detail.php?id=<?php echo urlencode((string)$communityId); ?>"
							class="community-card-link"
							aria-label="View <?php echo communityEscape($communityTitle); ?> details"
						></a>

						<div class="community-card-content">

							<h2 class="community-card-title">
								<?php echo communityEscape($communityTitle); ?>
							</h2>

							<p class="community-card-description">
								<?php echo communityEscape($communityDescription); ?>
							</p>

							<div class="community-card-footer">

								<div class="community-member-count">
									<i class="bi bi-people"></i>
									<span>
                                        <?php echo (int)$memberCount; ?> Members
                                    </span>
								</div>

								<button
									type="button"
									class="btn community-join-btn"
									onclick="event.preventDefault(); event.stopPropagation();"
								>
									Join
								</button>

							</div>

						</div>

					</article>
				<?php endforeach; ?>
			<?php endif; ?>
		</section>

		<!-- ==========================
			 Pagination
		=========================== -->

		<nav
			class="pagination-container"
			aria-label="Community pagination"
		>
			<ul
				id="community-pagination"
				class="pagination justify-content-center"
			></ul>
		</nav>
	</main>

	<!-- Bootstrap JavaScript -->
	<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
		crossorigin="anonymous"
	></script>

	<!-- Community Pagination -->
	<script src="components/pagination.js"></script>

	<script>
        document.addEventListener("DOMContentLoaded", function () {
            const communityPagination = createPagination({
                itemsPerPage: 8,
                paginationId: "community-pagination"
            });

            communityPagination.setItems(
                document.querySelectorAll(".community-card")
            );
        });
	</script>

	<!-- Sorting Dropdown Behavior -->
	<script>
        document.addEventListener("DOMContentLoaded", function () {
            const dropdowns = document.querySelectorAll(
                ".community-sort-dropdown"
            );

            dropdowns.forEach(function (dropdown) {
                dropdown.addEventListener("toggle", function () {
                    if (dropdown.open) {
                        dropdowns.forEach(function (otherDropdown) {
                            if (otherDropdown !== dropdown) {
                                otherDropdown.removeAttribute("open");
                            }
                        });
                    }
                });
            });

            document.addEventListener("click", function (event) {
                dropdowns.forEach(function (dropdown) {
                    if (!dropdown.contains(event.target)) {
                        dropdown.removeAttribute("open");
                    }
                });
            });
        });
	</script>

	</body>
</html>