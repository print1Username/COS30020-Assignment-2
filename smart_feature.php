<?php require_once __DIR__."/smart_feature_db.php"; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<link rel="icon" href="public/logo.ico" sizes="any">
		<link rel="icon" type="image/png" href="public/logo.png">
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Find a Board Game | Boardgame Hub</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="style/navbar.css">
	</head>
	<body>
		<?php include "navbar.php"; ?>
		<main class="container py-5">
			<a href="main_menu.php">Back to Main Menu</a>
			<h1 class="mt-3">Find a Board Game</h1>
			<p>Search by board game name or category to find an approved game.</p>
			<form method="get" action="smart_feature.php" class="row g-2 mb-4">
				<div class="col-sm-8 col-md-6">
					<label for="search" class="form-label">Name or category</label>
					<input id="search" name="search" class="form-control" value="<?php echo smartFeatureEscape($search); ?>" maxlength="50" required>
				</div>
				<div class="col-sm-auto d-flex align-items-end">
					<button type="submit" class="btn btn-primary">Find Game</button>
				</div>
			</form>

			<?php if ($search !== "" && $result === null): ?>
				<p class="alert alert-info" role="status">No matching data available.</p>
			<?php elseif ($result !== null): ?>
				<article class="card" style="max-width: 32rem;">
					<?php if ($image = smartFeatureAsset($result["item_image"])): ?>
						<img src="<?php echo smartFeatureEscape($image); ?>" class="card-img-top" alt="<?php echo smartFeatureEscape($result["item_name"]); ?>" style="max-height: 20rem; object-fit: contain;">
					<?php else: ?>
						<p class="card-header mb-0">Image not available</p>
					<?php endif; ?>
					<div class="card-body">
						<h2 class="card-title h4"><?php echo smartFeatureEscape($result["item_name"]); ?></h2>
						<p class="card-text">Category: <?php echo smartFeatureEscape($result["item_category"]); ?></p>
						<?php if ($pdf = smartFeatureAsset($result["description_file"])): ?>
							<a class="btn btn-primary" href="<?php echo smartFeatureEscape($pdf); ?>" download>Download PDF Description</a>
						<?php else: ?>
							<p class="text-muted mb-0">PDF description not available.</p>
						<?php endif; ?>
					</div>
				</article>
			<?php endif; ?>
		</main>
	</body>
</html>
