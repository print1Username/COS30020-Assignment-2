<?php require_once __DIR__."/smart_feature_db.php"; ?>
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
		<title >Find a Board Game | Boardgame Hub</title >
		<link
			href = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel = "stylesheet"
		>
		<link
			rel = "stylesheet"
			href = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		>
		<link
			rel = "stylesheet"
			href = "style/navbar.css"
		>
		<link
			rel = "stylesheet"
			href = "style/smart_feature_css.css"
		>
	</head >
	<body class = "smart-feature-page">
	<?php include "navbar.php"; ?>
	<main >
		<header class = "smart-feature-hero">
			<div class = "container">
				<h1 >Find a Board Game</h1 >
				<p >Search approved games or receive a recommendation based on your group and available time.</p >
			</div >
		</header >
		<section class = "smart-feature-section">
			<div class = "container">
				<form
					method = "get"
					action = "smart_feature.php"
					class = "smart-feature-panel smart-feature-search-form"
				>
					<div class = "smart-feature-search-field">
						<label
							for = "search"
							class = "form-label"
						>Name or category
						</label >
						<input
							id = "search"
							name = "search"
							class = "form-control"
							value = "<?php echo smartFeatureEscape($search); ?>"
							maxlength = "50"
							required
						>
					</div >
					<button
						type = "submit"
						class = "btn smart-feature-button"
					>Find Game
					</button >
				</form >
				<p class = "smart-feature-contribute-link">Want to Contribute a Board Game?
					<a
						href = "smart_feature_add.php"
					>Click Here
					</a >
				</p >
				<section class = "smart-feature-panel smart-feature-recommendation">
					<h2 >Board Game Recommendation</h2 >
					<p >Choose your group size, maximum playing time and optional categories.</p >
					<form method = "get" action = "smart_feature.php">
						<input type = "hidden" name = "recommend" value = "1">
						<div class = "row g-3">
							<div class = "col-sm-6">
								<label class = "form-label" for = "player-count">Players</label >
								<input class = "form-control" id = "player-count" name = "player_count" type = "number" min = "1" max = "99" required value = "<?php echo smartFeatureEscape($recommendationPlayers); ?>">
							</div >
							<div class = "col-sm-6">
								<label class = "form-label" for = "max-duration">Maximum playing time (minutes)</label >
								<input class = "form-control" id = "max-duration" name = "max_duration" type = "number" min = "1" max = "999" required value = "<?php echo smartFeatureEscape($recommendationDuration); ?>">
							</div >
							<fieldset class = "col-12">
								<legend class = "form-label fs-6">Category preferences (optional)</legend >
								<?php foreach ( $availablePreferences as $preference ): ?>
									<div class = "form-check form-check-inline">
										<input class = "form-check-input" type = "checkbox" id = "preference-<?php echo smartFeatureEscape(strtolower(str_replace(' ', '-', $preference))); ?>" name = "preferences[]" value = "<?php echo smartFeatureEscape($preference); ?>" <?php echo in_array($preference, $selectedPreferences, true) ? 'checked' : ''; ?>>
										<label class = "form-check-label" for = "preference-<?php echo smartFeatureEscape(strtolower(str_replace(' ', '-', $preference))); ?>"><?php echo smartFeatureEscape($preference); ?></label >
									</div >
								<?php endforeach; ?>
							</fieldset >
						</div >
						<button class = "btn smart-feature-button mt-3" type = "submit">Recommend Games</button >
					</form >
				</section >
				<?php if ( isset($_GET['recommend']) ): ?>
					<?php if ( $recommendationError !== '' ): ?>
						<p class = "smart-feature-message" role = "status"><?php echo smartFeatureEscape($recommendationError); ?></p >
					<?php elseif ( !$recommendations ): ?>
						<p class = "smart-feature-message" role = "status">No approved catalogue games match these preferences. Try a longer playing time or fewer category preferences.</p >
					<?php else: ?>
						<section class = "smart-feature-recommendation-results" aria-label = "Recommended board games">
							<h2 >Recommended Games</h2 >
							<div class = "smart-feature-recommendation-grid">
								<?php foreach ( $recommendations as $recommendation ): ?>
									<article class = "smart-feature-recommendation-card">
										<?php if ( $recommendation['image_url'] ): ?><img src = "<?php echo smartFeatureEscape($recommendation['image_url']); ?>" alt = "<?php echo smartFeatureEscape($recommendation['item_name']); ?>"><?php endif; ?>
										<h3 ><?php echo smartFeatureEscape($recommendation['item_name']); ?></h3 >
										<p ><?php echo smartFeatureEscape($recommendation['item_category']); ?></p >
										<p ><?php echo smartFeatureEscape($recommendation['players']); ?> · <?php echo smartFeatureEscape($recommendation['duration']); ?></p >
									</article >
								<?php endforeach; ?>
							</div >
						</section >
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( $search !== "" && $result === null ): ?>
					<p
						class = "smart-feature-message"
						role = "status"
					>No matching data available.
					</p >
				<?php elseif ( $result !== null ): ?>
					<article class = "smart-feature-panel smart-feature-result">
						<?php if ( $image = $result["image_url"] ): ?>
							<img
								src = "<?php echo smartFeatureEscape($image); ?>"
								class = "smart-feature-result-image"
								alt = "<?php echo smartFeatureEscape($result["item_name"]); ?>"
							>
						<?php else: ?>
							<p class = "smart-feature-image-fallback">Image not available</p >
						<?php endif; ?>
						<div class = "smart-feature-result-body">
							<h2 ><?php echo smartFeatureEscape($result["item_name"]); ?></h2 >
							<p >Category: <?php echo smartFeatureEscape($result["item_category"]); ?></p >
							<?php if ( !empty($result['item_description']) ): ?>
								<p ><?php echo smartFeatureEscape($result['item_description']); ?></p >
							<?php endif; ?>
							<?php if ( !empty($result['players']) && !empty($result['duration']) ): ?>
								<p ><?php echo smartFeatureEscape($result['players']); ?> · <?php echo smartFeatureEscape($result['duration']); ?></p >
							<?php endif; ?>
							<?php if ( $pdf = smartFeatureAsset($result["description_file"]) ): ?>
								<a
									class = "btn smart-feature-button"
									href = "<?php echo smartFeatureEscape($pdf); ?>"
									download
								>Download PDF Description
								</a >
							<?php else: ?>
								<p class = "smart-feature-muted">PDF description not available.</p >
							<?php endif; ?>
						</div >
					</article >
				<?php endif; ?>
			</div >
		</section >
	</main >
	</body >
</html >
