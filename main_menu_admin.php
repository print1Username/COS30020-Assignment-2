<?php require_once __DIR__."/admin_db.php"; ?>
<!DOCTYPE html>
<html lang = "en">
	<head >
		<link
			rel = "icon"
			href = "img/logo/logo.ico"
			sizes = "any"
		>
		<link
			rel = "icon"
			type = "image/png"
			href = "img/logo/logo.png"
		>
		<meta charset = "utf-8">
		<meta
			name = "viewport"
			content = "width=device-width, initial-scale=1"
		>
		<title >Admin Menu | Boardgame Hub</title >
		<link
			href = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel = "stylesheet"
		>
		<link rel = "stylesheet" href = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
		<link
			rel = "stylesheet"
			href = "style/admin_theme.css?v=<?php echo filemtime(__DIR__."/style/admin_theme.css"); ?>"
		>
	</head >
	<body >
	<?php include __DIR__."/admin_nav.php"; ?>
	<header class = "admin-hero">
		<div class = "container">
			<p class = "text-uppercase text-secondary fw-bold">Boardgame Hub</p >
			<h1 >Admin Main Menu</h1 >
			<p class = "text-secondary">Manage accounts, catalog, community contributions and activity registrations.</p >
		</div >
	</header >
	<main class = "container py-5">
		<div class = "row g-4">
			<div class = "col-md-6">
				<article class = "admin-card">
					<h2 >Manage User Accounts</h2 >
					<p >Add, edit or delete user accounts.</p >
					<a
						class = "btn admin-action"
						href = "manage_accounts.php"
					>Manage
						Accounts
					</a >
				</article >
			</div >
			<div class = "col-md-6">
                <article class = "admin-card">
                    <h2 >Manage Catalog</h2 >
                    <p >Create, edit or delete board games in the catalog.</p >
                    <a class = "btn admin-action" href = "manage_catalog.php">Manage Catalog</a >
                </article >
            </div >
			<div class = "col-md-6">
				<article class = "admin-card">
					<h2 >Manage Communities</h2 >
					<p >Manage communities, membership requests and posts.</p >
					<div class = "admin-community-actions">
						<a
							class = "btn admin-action"
							href = "manage_community.php?tab=create"
						>Manage Communities
						</a >
						<a
							class = "btn admin-action"
							href = "manage_community.php?tab=join"
						>Review Join Requests
						</a >
						<a
							class = "btn admin-action"
							href = "manage_community.php?tab=post"
						>Manage Community Posts
						</a >
					</div >
				</article >
			</div >
			<div class = "col-md-6">
				<article class = "admin-card">
					<h2 >Manage Activities</h2 >
					<p >Add, edit, delete or review activity registrations.</p >
					<div class = "admin-community-actions">
						<a
							class = "btn admin-action"
							href = "manage_activities.php?tab=activities"
						>Manage Activities
						</a >
						<a
							class = "btn admin-action"
							href = "manage_activities.php?tab=registrations"
						>Manage Registrations
						</a >
					</div >
				</article >
			</div >
		</div >
		<a class = "admin-about-card" href = "about.php">
			<div class = "admin-about-content">
				<i class = "bi bi-info-circle-fill" aria-hidden = "true"></i >
				<div >
					<h2 >About Us</h2 >
					<p >Learn more about Boardgame Hub, its purpose, features, and project information.</p >
				</div >
			</div >
			<span class = "btn admin-about-button">Learn More <i class = "bi bi-arrow-right" aria-hidden = "true"></i ></span >
		</a >
    </main >
	<script src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script >
	</body >
</html >
