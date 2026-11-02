<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>My Website</title>

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

		<link
			rel="stylesheet"
			href="style/styles.css"
		>
	</head>

	<body>
	<!-- Navbar will be inserted here -->
	<div id="navbar-container"></div>

	<?php include "navbar.php"; ?>

	<!-- Page Content -->
	<main class="container py-5">

		<!-- Hero Section -->
		<section class="index-hero text-center">

			<div class="index-hero-content">

                    <span class="badge index-badge mb-3">
                        BOARD GAME VENUE
                    </span>

				<h1 class="display-4 fw-bold">
					Welcome to Boardgame Hub
				</h1>

				<p class="lead index-hero-text">
					Discover board games, book a table,
					and enjoy your next game session.
				</p>

				<p class="index-description mx-auto">
					Boardgame Hub makes it easier for board game players
					to discover games, check table availability, and
					reserve a suitable time at our venue.
				</p>

				<div class="index-hero-buttons mt-4">

					<a href="main_menu.php" class="btn btn-primary btn-lg">
						Explore Board Games
					</a>
				</div>
			</div>
		</section>


		<!-- Random Image -->
		<section class="index-image-section">
			<div class="index-image-wrapper">
				<img
					src="img/boardgame-1.jpg"
					alt="Board game session at Boardgame Hub"
					class="index-random-image"
					id="boardgame-slideshow"
				>

			</div>

		</section>


		<!-- Features -->
		<section class="index-features">

			<div class="text-center mb-4">

				<h2 class="fw-bold">
					Everything You Need to Play
				</h2>

				<p class="text-muted">
					Explore games, book your table, and connect with
					fellow board game players.
				</p>

			</div>


			<div class="row g-4">

				<!-- Catalogue -->
				<div class="col-md-4">

					<div class="card index-feature-card h-100">

						<div class="card-body text-center">

							<div class="index-feature-icon">
								<i class="bi bi-grid-3x3-gap-fill"></i>
							</div>

							<h3 class="h5 fw-bold">
								Discover Games
							</h3>

							<p class="text-muted">
								Browse our collection of board games and
								discover something new to play.
							</p>

							<a
								href="catalog.php"
								class="btn btn-outline-primary"
							>
								View Games
							</a>

						</div>

					</div>

				</div>


				<!-- Activities -->
				<div class="col-md-4">

					<div class="card index-feature-card h-100">

						<div class="card-body text-center">

							<div class="index-feature-icon">
								<i class="bi bi-calendar-check-fill"></i>
							</div>

							<h3 class="h5 fw-bold">
								Book a Table
							</h3>

							<p class="text-muted">
								Check available dates and times and
								reserve a table for your next session.
							</p>

							<a
								href="activities.php"
								class="btn btn-outline-primary"
							>
								View Activities
							</a>

						</div>

					</div>

				</div>


				<!-- Community -->
				<div class="col-md-4">

					<div class="card index-feature-card h-100">

						<div class="card-body text-center">

							<div class="index-feature-icon">
								<i class="bi bi-people-fill"></i>
							</div>

							<h3 class="h5 fw-bold">
								Join the Community
							</h3>

							<p class="text-muted">
								Explore board game discussions and
								stories shared by other players.
							</p>

							<a
								href="community.php"
								class="btn btn-outline-primary"
							>
								Visit Community
							</a>

						</div>

					</div>

				</div>

			</div>

		</section>


		<!-- Why Boardgame Hub -->
		<section class="index-about-section">

			<div class="row align-items-center g-5">

				<div class="col-md-6">

                        <span class="index-section-label">
                            WHY BOARDGAME HUB?
                        </span>

					<h2 class="fw-bold mt-2">
						Make your next game night easier.
					</h2>

				</div>

				<div class="col-md-6">

					<p class="text-muted mb-0">
						Planning a board game session can be difficult when
						players need to coordinate their schedules and find
						an available table. Boardgame Hub provides a simple
						way to check available sessions and reserve a suitable
						time before visiting the venue.
					</p>

				</div>

			</div>

		</section>


		<!-- Login CTA -->
		<section class="index-cta text-center">

			<h2 class="fw-bold">
				Ready for your next game?
			</h2>

			<p class="mb-4">
				Log in to manage your bookings and profile.
			</p>

			<?php
			if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
				$loginLink = "profile.php";
			} else {
				$loginLink = "login.php";
			}
			?>

			<a href="<?php echo $loginLink; ?>" class="index-login-link">
				Log In
			</a>

		</section>


		<!-- About -->
		<section class="text-center index-about-link">

			<a href="about.php">
				About Boardgame Hub
			</a>

		</section>

	</main>


	<!-- Bootstrap JavaScript -->
	<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
		crossorigin="anonymous"
	></script>

	<!-- Pass PHP Session Login Status to JavaScript -->
	<script>
        window.isLoggedIn = <?php
		echo (
			isset($_SESSION["logged_in"]) &&
			$_SESSION["logged_in"] === true
		) ? "true" : "false";
		?>;

        let currentImage = 1;
        const totalImages = 5;

        setInterval(function () {
            currentImage++;

            if (currentImage > totalImages) {
                currentImage = 1;
            }

            document.getElementById("boardgame-slideshow").src =
                "img/boardgame-" + currentImage + ".jpg";

        }, 5000);
	</script>
	</body>

</html>