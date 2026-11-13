<?php require_once __DIR__."/admin_db.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="public/logo.ico" sizes="any">
    <link rel="icon" type="image/png" href="public/logo.png">
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin Menu | Boardgame Hub</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="style/admin_theme.css?v=<?php echo filemtime(__DIR__."/style/admin_theme.css"); ?>"></head>
<body>
<?php include __DIR__."/admin_nav.php"; ?>
<header class="admin-hero"><div class="container"><p class="text-uppercase text-secondary fw-bold">Boardgame Hub</p><h1>Admin Main Menu</h1><p class="text-secondary">Manage accounts, community contributions and activity registrations.</p></div></header>
<main class="container py-5"><div class="row g-4">
	<div class="col-md-4"><article class="admin-card"><h2>Manage User Accounts</h2><p>Add, edit or delete user accounts.</p><a class="btn admin-action" href="manage_accounts.php">Manage Accounts</a></article></div>
	<div class="col-md-4"><article class="admin-card"><h2>Manage Communities</h2><p>Add, edit, delete or review community contributions.</p><a class="btn admin-action" href="manage_community.php">Manage Communities</a></article></div>
	<div class="col-md-4"><article class="admin-card"><h2>Manage Activities</h2><p>Add, edit, delete or review activity registrations.</p><a class="btn admin-action" href="manage_activities.php">Manage Activities</a></article></div>
</div></main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
