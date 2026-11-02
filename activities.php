<?php
session_start();

$activitiesFile = "data/activities.json";
$activities = [];

if (file_exists($activitiesFile)) {
	$jsonData = file_get_contents($activitiesFile);
	$activities = json_decode($jsonData, true);

	if (!is_array($activities)) {
		$activities = [];
	}
}

$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$registerLink = $isLoggedIn ? "activity_reg.php" : "login.php?message=login_required";
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Activities | Boardgame Hub</title>

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
		<link
			rel="stylesheet"
			href="style/navbar.css"
		>

		<!-- Activities CSS -->
		<link
			rel="stylesheet"
			href="style/activities.css"
		>

		<!-- Pagination CSS -->
		<link
			rel="stylesheet"
			href="style/pagination.css"
		>
	</head>

	<body>
	<?php include "navbar.php"; ?>

	<main class="container py-5">
		<div class="activities-header">
			<div>
				<span class="activities-eyebrow">BOARDGAME HUB</span>
				<h1 class="activities-title">Activities</h1>
				<p class="activities-subtitle">
					Discover upcoming board game sessions, meet other players and join the community.
				</p>
			</div>

			<div class="activities-header-action">
				<a
					href="#"
					class="btn activities-register-btn"
				>
					<i class="bi bi-calendar-plus me-2"></i>
					Register for Activity
				</a>
			</div>
		</div>

		<?php if (empty($activities)): ?>
			<div class="alert alert-light border activities-empty-alert" role="alert">
				<i class="bi bi-calendar-x me-2"></i>
				No activities are currently available.
			</div>
		<?php else: ?>
			<div
				id="activity-list"
				class="activities-list"
			>
				<?php foreach ($activities as $activity): ?>
					<?php
					$image = isset($activity["image"]) ? $activity["image"] : null;
					$hasImage = !empty($image) && file_exists($image);
					?>

					<article
						class="activity-card"
						hidden
					>
						<?php
						$activityId = isset($activity["id"]) ? (int)$activity["id"] : 0;

						if ($isLoggedIn) {
							$activityRegisterLink = "activity_reg.php?id=".$activityId;
						} else {
							$activityRegisterLink = "login.php?message=login_required";
						}
						?>

						<a
							href="<?php echo htmlspecialchars($activityRegisterLink); ?>"
							class="activity-card-link"
							aria-label="Register for <?php echo htmlspecialchars($activity["title"]); ?>"
						>
							<div class="activity-card-image">
								<?php if ($hasImage): ?>
									<img
										src="<?php echo htmlspecialchars($image); ?>"
										alt="<?php echo htmlspecialchars($activity["title"]); ?>"
										class="activity-image"
									>
								<?php else: ?>
									<div
										class="activity-default-image"
										aria-label="Default activity image"
									>
										<i class="bi bi-image"></i>
										<span>Boardgame Hub</span>
									</div>
								<?php endif; ?>
							</div>

							<div class="activity-card-content">
								<div class="activity-card-top">
                                        <span class="activity-type">
                                            <?php echo htmlspecialchars($activity["type"]); ?>
                                        </span>

									<span class="activity-price">
                                            RM <?php echo number_format((float)$activity["price"], 2); ?>
                                        </span>
								</div>

								<h2 class="activity-card-title">
									<?php echo htmlspecialchars($activity["title"]); ?>
								</h2>

								<p class="activity-description">
									<?php echo htmlspecialchars($activity["description"]); ?>
								</p>

								<div class="activity-details">
									<div class="activity-detail-item">
										<i class="bi bi-calendar-event-fill"></i>
										<div>
											<span class="activity-detail-label">Date</span>
											<span class="activity-detail-value">
                                                    <?php
													echo htmlspecialchars(
														date(
															"d M Y",
															strtotime($activity["date"])
														)
													);
													?>
                                                </span>
										</div>
									</div>

									<div class="activity-detail-item">
										<i class="bi bi-clock-fill"></i>
										<div>
											<span class="activity-detail-label">Time</span>
											<span class="activity-detail-value">
                                                    <?php
													echo htmlspecialchars(
														date(
															"g:i A",
															strtotime($activity["start_time"])
														)
													);
													?>
                                                    -
                                                    <?php
													echo htmlspecialchars(
														date(
															"g:i A",
															strtotime($activity["end_time"])
														)
													);
													?>
                                                </span>
										</div>
									</div>

									<div class="activity-detail-item">
										<i class="bi bi-dice-5-fill"></i>
										<div>
											<span class="activity-detail-label">Table</span>
											<span class="activity-detail-value">
                                                    <?php echo htmlspecialchars($activity["table"]); ?>
                                                </span>
										</div>
									</div>

									<div class="activity-detail-item">
										<i class="bi bi-geo-alt-fill"></i>
										<div>
											<span class="activity-detail-label">Venue</span>
											<span class="activity-detail-value">
                                                    <?php echo htmlspecialchars($activity["venue"]); ?>
                                                </span>
										</div>
									</div>
								</div>
							</div>
						</a>

						<div class="activity-card-footer">
                                <span class="activity-footer-info">
                                    <i class="bi bi-people-fill me-1"></i>
                                    Join the Boardgame Hub community
                                </span>
							<a
								href="<?php echo htmlspecialchars($activityRegisterLink); ?>"
								class="btn activity-register-card-btn"
							>
								<i class="bi bi-calendar-check me-2"></i>
								Register
							</a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<nav
				class="activity-pagination-wrapper"
				aria-label="Activities pagination"
			>
				<ul
					id="activity-pagination"
					class="pagination justify-content-center mb-0"
				></ul>
			</nav>
		<?php endif; ?>
	</main>

	<!-- Bootstrap JavaScript -->
	<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
		crossorigin="anonymous"
	></script>

	<!-- Pagination Component -->
	<script src="components/pagination.js"></script>

	<script>
        document.addEventListener("DOMContentLoaded", function () {
            const activityItems = document.querySelectorAll(".activity-card");

            if (activityItems.length > 0) {
                createPagination({
                    itemsPerPage: 6,
                    paginationId: "activity-pagination"
                }).setItems(activityItems);
            }
        });
	</script>
	</body>
</html>