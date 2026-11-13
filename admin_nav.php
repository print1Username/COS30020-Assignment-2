<nav class="navbar navbar-expand-lg navbar-dark admin-navbar">
	<div class="container">
		<a class="navbar-brand fw-bold" href="main_menu_admin.php">Boardgame Hub Admin</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
		<div class="collapse navbar-collapse" id="adminNav">
			<div class="navbar-nav me-auto">
				<a class="nav-link" href="main_menu_admin.php">Admin Menu</a>
				<a class="nav-link" href="manage_accounts.php">Accounts</a>
				<a class="nav-link" href="manage_community.php">Community</a>
				<a class="nav-link" href="manage_activities.php">Activities</a>
			</div>
			<div class="d-flex align-items-center gap-2">
				<a class="btn admin-account-button d-flex align-items-center gap-2" href="profile.php" aria-label="View Account Profile">
					<svg width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/></svg>
					<span>Account</span>
				</a>
				<a class="btn btn-danger" href="main_menu_admin.php?logout=1">Logout</a>
			</div>
		</div>
	</div>
</nav>
