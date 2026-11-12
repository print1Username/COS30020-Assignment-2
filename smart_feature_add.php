<?php
require_once __DIR__."/smart_feature_add_db.php";
?>
<!DOCTYPE html>
<html lang="en">
	<head>
    <link rel="icon" href="public/logo.ico" sizes="any">
    <link rel="icon" type="image/png" href="public/logo.png">

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Contribute a Board Game | Boardgame Hub</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="style/navbar.css">
		<link rel="stylesheet" href="style/smart_feature_css.css">
	</head>
	<body class="smart-feature-page">
	<?php include "navbar.php"; ?>
	<main>
		<header class="smart-feature-hero">
			<div class="container">
				<h1>Contribute a Board Game</h1>
				<p>Share a board game with the Boardgame Hub community.</p>
			</div>
		</header>
		<section class="smart-feature-section">
			<div class="container">
		<?php if (!$isUser): ?>
			<p class="smart-feature-panel smart-feature-message">Please <a href="login.php">log in as a user</a> to submit a contribution.</p>
		<?php else: ?>
			<?php if ($error !== ""): ?><div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
			<?php if ($success !== ""): ?><div class="alert alert-success" role="status"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
			<form method="post" action="smart_feature_add.php" enctype="multipart/form-data" class="smart-feature-panel smart-feature-contribution-form">
				<div class="mb-3">
					<label for="item_name" class="form-label">Board game name</label>
					<input id="item_name" name="item_name" class="form-control" maxlength="50" value="<?php echo htmlspecialchars($itemName); ?>" required>
				</div>
				<div class="mb-3">
					<label for="item_category" class="form-label">Category</label>
					<input id="item_category" name="item_category" class="form-control" maxlength="50" value="<?php echo htmlspecialchars($itemCategory); ?>" required>
				</div>
				<div class="mb-3">
					<label for="item_image" class="form-label">Board game image (optional, JPG or PNG, max 5MB)</label>
					<input type="file" id="item_image" name="item_image" class="form-control" accept="image/jpeg,image/png">
				</div>
				<div class="mb-3">
					<label for="description_file" class="form-label">PDF description (max 7MB)</label>
					<input type="file" id="description_file" name="description_file" class="form-control" accept="application/pdf" required>
				</div>
				<button type="submit" class="btn smart-feature-button">Submit for review</button>
			</form>
		<?php endif; ?>
			</div>
		</section>
	</main>
	</body>
</html>
