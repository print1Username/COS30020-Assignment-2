<?php
require_once __DIR__."/community_db.php";
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<link rel="icon" href="img/logo/logo.ico" sizes="any">
		<link rel="icon" type="image/png" href="img/logo/logo.png">

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
		<link rel="stylesheet" href="style/community.css?v=7">

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

		</section>

		<!-- ==========================
			 Filter and Sorting Toolbar
		=========================== -->

		<section class="community-toolbar">

			<div class="community-control-group">

				<span class="community-control-heading">
					<i class="bi bi-funnel" aria-hidden="true"></i>
					<span class="community-control-label">Filter:</span>
				</span>

				<!-- Membership Filter -->
				<details class="community-sort-dropdown">

					<summary class="community-sort-trigger">
						<span>Membership: <?php echo match ($membershipFilter) {
								"joined" => "Joined",
								"pending" => "Pending",
								"not_joined" => "Not Joined",
								default => "All"
							}; ?></span>
						<i class="bi bi-chevron-down community-sort-chevron"></i>
					</summary>

					<div class="community-sort-options">

						<form method="GET" action="community.php">

							<input type="hidden" name="sort_type" value="<?php echo e($sortType); ?>">
							<input type="hidden" name="sort_order" value="<?php echo e($sortOrder); ?>">

							<?php
							foreach (["all" => "All", "joined" => "Joined", "pending" => "Pending", "not_joined" => "Not Joined"]
							         as $filterValue => $filterLabel): ?>
								<label class="community-sort-option">
									<input
										type="radio"
										name="membership_filter"
										value="<?php echo e($filterValue); ?>"
										<?php echo communitySelected($membershipFilter, $filterValue); ?>
										onchange="this.form.submit()"
									>
									<span><?php echo e($filterLabel); ?></span>
								</label>
							<?php endforeach; ?>

						</form>

					</div>

				</details>

				<span class="community-control-divider" aria-hidden="true"></span>

				<div class="community-sort-group">
					<span class="community-control-heading">
						<i class="bi bi-sort-down" aria-hidden="true"></i>
						<span class="community-control-label">Sorting:</span>
					</span>

					<!-- Sorting Type -->
					<details class="community-sort-dropdown">

						<summary class="community-sort-trigger">
							<span>Type: <?php echo $sortType === "alphabetical" ? "Alphabetical" : "Time"; ?></span>
							<i class="bi bi-chevron-down community-sort-chevron"></i>
						</summary>

						<div class="community-sort-options">

							<form method="GET" action="community.php">

								<input
									type="hidden"
									name="membership_filter"
									value="<?php echo e($membershipFilter); ?>"
								>

								<input
									type="hidden"
									name="sort_order"
									value="<?php echo e($sortOrder); ?>"
								>

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

							</form>

						</div>

					</details>

					<!-- Sorting Order -->
					<details class="community-sort-dropdown">

						<summary class="community-sort-trigger">
							<span>Direction: <?php echo $sortOrder === "descending" ? "Descending" : "Ascending"; ?></span>
							<i class="bi bi-chevron-down community-sort-chevron"></i>
						</summary>

						<div class="community-sort-options">

							<form method="GET" action="community.php">

								<input
									type="hidden"
									name="membership_filter"
									value="<?php echo e($membershipFilter); ?>"
								>

								<input
									type="hidden"
									name="sort_type"
									value="<?php echo e($sortType); ?>"
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
					$joinStatus = $community["join_status"] ?? null;
					?>

					<article class="community-card">

						<a
							href="community_detail.php?id=<?php echo urlencode((string)$communityId); ?>"
							class="community-card-link"
							aria-label="View <?php echo e($communityTitle); ?> details"
						></a>

						<div class="community-card-content">

							<h2 class="community-card-title">
								<?php echo e($communityTitle); ?>
							</h2>

							<p class="community-card-description">
								<?php echo e($communityDescription); ?>
							</p>

							<div class="community-card-footer">

								<div class="community-member-count">
									<i class="bi bi-people"></i>
									<span>
                                        <?php echo (int)$memberCount; ?> Members
                                    </span>
								</div>

								<?php if ($joinStatus === "pending"): ?>
									<span
										class="community-membership-icon community-membership-pending" role="img"
										aria-label="Membership pending admin approval" title="Pending approval"
									>
										<i class="bi bi-hourglass-split" aria-hidden="true"></i>
									</span>
								<?php elseif ($joinStatus === "approved"): ?>
									<span
										class="community-membership-icon community-membership-approved" role="img"
										aria-label="Already in community" title="Already in community"
									>
										<i class="bi bi-check-circle" aria-hidden="true"></i>
									</span>
								<?php else: ?>
									<button
										type="button"
										class="btn community-join-btn"
										onclick="event.preventDefault(); event.stopPropagation();"
									>
										Join
									</button>
								<?php endif; ?>

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

	<!-- Community Behavior -->
	<script src="components/community.js?v=<?= filemtime(__DIR__."/components/community.js") ?>"></script>

	</body>
</html>
