<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>About Us | Boardgame Hub</title>
		<link
			href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
			integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"
		>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
		<link rel="stylesheet" href="style/navbar.css">
		<link rel="stylesheet" href="style/about.css">
	</head>
	<body>
	<?php include "navbar.php"; ?>

	<main class="about-page">
		<section class="about-hero">
			<div class="container about-hero-content">
				<span class="about-eyebrow">ABOUT BOARDGAME HUB</span>
				<h1>Making game nights easier to plan.</h1>
				<p>Discover board games, reserve a table, and connect with fellow players in one welcoming place.</p>
			</div>
		</section>

		<div class="container about-content">
			<section class="row g-4" aria-label="Project overview">
				<div class="col-lg-7">
					<article class="about-card h-100">
						<div class="about-card-icon"><i class="bi bi-people-fill"></i></div>
						<span class="about-card-label">THE PROBLEM WE SOLVE</span>
						<h2>Planning a board game session should be simple.</h2>
						<p>Board game players can find it difficult to choose a game, coordinate a suitable time, and
							secure an available table. Boardgame Hub brings the catalogue, activities, table booking,
							and community showcase together so players can spend less time planning and more time
							playing.</p>
						<p class="mb-0">This project was chosen to support a growing community of board game players and
							make a venue-based game session easier to organise.</p>
					</article>
				</div>
				<div class="col-lg-5">
					<article class="about-card h-100">
						<div class="about-card-icon"><i class="bi bi-code-slash"></i></div>
						<span class="about-card-label">TECHNOLOGY USED</span>
						<h2>Built for this assignment.</h2>
						<ul class="about-tech-list">
							<li><i class="bi bi-check-circle-fill"></i>PHP <?php echo htmlspecialchars(phpversion()); ?>
							</li>
							<li><i class="bi bi-check-circle-fill"></i>HTML5 and custom CSS</li>
							<li><i class="bi bi-check-circle-fill"></i>Bootstrap 5.3.8</li>
							<li><i class="bi bi-check-circle-fill"></i>Bootstrap Icons 1.11.3</li>
							<li><i class="bi bi-check-circle-fill"></i>JSON and plain-text data files</li>
						</ul>
					</article>
				</div>
			</section>

			<section class="about-section" aria-labelledby="progress-heading">
				<div class="about-section-heading">
					<span class="about-eyebrow">ASSIGNMENT PROGRESS</span>
					<h2 id="progress-heading">What has been developed</h2>
					<p>Core pages and account features have been implemented for Boardgame Hub.</p>
				</div>
				<div class="row g-4">
					<div class="col-lg-7">
						<article class="about-card about-progress-card h-100">
							<h3><i class="bi bi-check2-circle"></i>Completed and implemented</h3>
							<ul class="about-progress-list">
								<li>Home page, main menu, catalogue, and activity listing</li>
								<li>Community showcase and community detail pages</li>
								<li>Registration, validation, login, and session handling</li>
								<li>Profile viewing and profile updating</li>
								<li>Activity registration with validation and duplicate checks</li>
								<li>This About Us page and video presentation</li>
							</ul>
						</article>
					</div>
					<div class="col-lg-5">
						<article class="about-card about-future-card h-100">
							<h3><i class="bi bi-hourglass-split"></i>Planned for Assignment 2</h3>
							<ul class="about-progress-list">
								<li>Smart feature or recommendation tool</li>
								<li>Community contribution upload feature</li>
								<li>Ordering or purchasing workflow</li>
							</ul>
							<p class="about-note mb-0">These features are intentionally reserved for Assignment 2, as
								specified in the assignment brief.</p>
						</article>
					</div>
				</div>
			</section>

			<section class="about-video-section" aria-labelledby="video-heading">
				<div class="about-section-heading text-center">
					<span class="about-eyebrow">VIDEO PRESENTATION</span>
					<h2 id="video-heading">See Boardgame Hub in action</h2>
					<p>A short demonstration of the website's main functionality.</p>
				</div>
				<div class="about-video-card">
					<div class="ratio ratio-16x9">
						<iframe
							src="https://www.youtube.com/embed/eAg9tXyo378?si=4XxJ37dO7751WhBq"
							title="Boardgame Hub video presentation"
							allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
							referrerpolicy="strict-origin-when-cross-origin" allowfullscreen
						></iframe>
					</div>
					<a
						href="https://www.youtube.com/watch?v=eAg9tXyo378" class="btn about-video-link" target="_blank"
						rel="noopener noreferrer"
					><i class="bi bi-youtube"></i>Watch on YouTube</a>
				</div>
			</section>

			<div class="about-home-link text-center">
				<a href="index.php" class="btn about-home-button"><i class="bi bi-house-door-fill"></i>Back to Home</a>
			</div>
		</div>
	</main>

	<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"
	></script>
	</body>
</html>
