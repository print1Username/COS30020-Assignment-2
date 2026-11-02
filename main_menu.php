<?php
session_start();

$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
$loginTarget = $isLoggedIn ? "profile.php" : "login.php";
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Welcome to Boardgame Hub</title>

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

		<!-- Navbar CSS -->
		<link
			rel="stylesheet"
			href="style/navbar.css"
		>

		<!-- Main Menu CSS -->
		<link
			rel="stylesheet"
			href="style/main_menu.css"
		>
	</head>

	<body>
	<!-- Navbar -->
	<?php include "navbar.php"; ?>

	<main>
		<!-- Hero Section -->
		<section class="main-menu-hero">
			<div class="container">
				<div class="main-menu-hero-content">
					<span class="main-menu-eyebrow">BOARDGAME HUB</span>
					<h1>Welcome to Boardgame Hub</h1>
					<p>
						Discover board games, join activities, connect with
						the community, and enjoy your next game session.
					</p>
				</div>
			</div>
		</section>

		<!-- Main Menu Cards -->
		<section class="main-menu-section">
			<div class="container">
				<div class="main-menu-grid">
					<!-- Catalogue Card -->
					<article class="main-menu-card">
						<div class="main-menu-card-icon">
							<i class="bi bi-grid-3x3-gap-fill"></i>
						</div>
						<div class="main-menu-card-content">
							<h2>Catalogue</h2>
							<p>
								Explore our collection of board games and
								discover something new to play.
							</p>
							<a
								href="catalog.php"
								class="btn main-menu-card-button"
							>
								View Catalogue
								<i class="bi bi-arrow-right"></i>
							</a>
						</div>
					</article>

					<!-- Activities Card -->
					<article class="main-menu-card">
						<div class="main-menu-card-icon">
							<i class="bi bi-calendar-event-fill"></i>
						</div>
						<div class="main-menu-card-content">
							<h2>Activities</h2>
							<p>
								Find upcoming board game activities,
								sessions, and events you can join.
							</p>
							<a
								href="activities.php"
								class="btn main-menu-card-button"
							>
								View Activities
								<i class="bi bi-arrow-right"></i>
							</a>
						</div>
					</article>

					<!-- Community Card -->
					<article class="main-menu-card">
						<div class="main-menu-card-icon">
							<i class="bi bi-people-fill"></i>
						</div>
						<div class="main-menu-card-content">
							<h2>Community</h2>
							<p>
								Explore contributions and see what other
								board game players are sharing.
							</p>
							<a
								href="community.php"
								class="btn main-menu-card-button"
							>
								View Community
								<i class="bi bi-arrow-right"></i>
							</a>
						</div>
					</article>

					<!-- About Card -->
					<article class="main-menu-card">
						<div class="main-menu-card-icon">
							<i class="bi bi-info-circle-fill"></i>
						</div>
						<div class="main-menu-card-content">
							<h2>About</h2>
							<p>
								Learn more about Boardgame Hub, its purpose,
								features, and project information.
							</p>
							<a
								href="about.php"
								class="btn main-menu-card-button"
							>
								Learn More
								<i class="bi bi-arrow-right"></i>
							</a>
						</div>
					</article>
				</div>
			</div>
		</section>

		<!-- Login Section -->
		<section class="main-menu-login-section">
			<div class="container">
				<div class="main-menu-login-card">
					<div class="main-menu-login-content">
						<i class="bi bi-person-circle"></i>
						<div>
							<h2>Ready to get started?</h2>
							<p>
								Log in to access your profile and manage
								your Boardgame Hub activities.
							</p>
						</div>
					</div>

					<a
						href="<?php echo $loginTarget; ?>"
						class="btn main-menu-login-button"
					>
						<i class="bi bi-box-arrow-in-right"></i>
						Login
					</a>
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

	<!-- Main Menu JavaScript -->
	<script src="components/main_menu.js"></script>
	</body>
</html>
