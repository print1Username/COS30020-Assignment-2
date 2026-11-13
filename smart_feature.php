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
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
		<link rel="stylesheet" href="style/navbar.css">
		<link rel="stylesheet" href="style/smart_feature_css.css">
	</head>
	<body class="smart-feature-page">
		<?php include "navbar.php"; ?>
		<main>
			<header class="smart-feature-hero">
				<div class="container">
					<h1>Find a Board Game</h1>
					<p>Search by board game name or category to find an approved game.</p>
				</div>
			</header>
			<section class="smart-feature-section">
				<div class="container">
				<form method="get" action="smart_feature.php" class="smart-feature-panel smart-feature-search-form">
				<div class="smart-feature-search-field">
					<label for="search" class="form-label">Name or category</label>
					<input id="search" name="search" class="form-control" value="<?php echo smartFeatureEscape($search); ?>" maxlength="50" required>
				</div>
				<button type="submit" class="btn smart-feature-button">Find Game</button>
			</form>
			<p class="smart-feature-contribute-link">Want to Contribute a Board Game? <a href="smart_feature_add.php">Click Here</a></p>

			<?php if ($search !== "" && $result === null): ?>
				<p class="smart-feature-message" role="status">No matching data available.</p>
			<?php elseif ($result !== null): ?>
				<article class="smart-feature-panel smart-feature-result">
					<?php if ($image = smartFeatureAsset($result["item_image"])): ?>
						<img src="<?php echo smartFeatureEscape($image); ?>" class="smart-feature-result-image" alt="<?php echo smartFeatureEscape($result["item_name"]); ?>">
					<?php else: ?>
						<p class="smart-feature-image-fallback">Image not available</p>
					<?php endif; ?>
					<div class="smart-feature-result-body">
						<h2><?php echo smartFeatureEscape($result["item_name"]); ?></h2>
						<p>Category: <?php echo smartFeatureEscape($result["item_category"]); ?></p>
						<?php if ($pdf = smartFeatureAsset($result["description_file"])): ?>
							<a class="btn smart-feature-button" href="<?php echo smartFeatureEscape($pdf); ?>" download>Download PDF Description</a>
						<?php else: ?>
							<p class="smart-feature-muted">PDF description not available.</p>
						<?php endif; ?>
					</div>
				</article>
			<?php endif; ?>
				</div>
			</section>
		</main>
	</body>
</html>
