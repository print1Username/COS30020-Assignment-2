<?php
require_once __DIR__."/manage_catalog_db.php";
require_once __DIR__."/admin_disclosure.php";
require_once __DIR__."/admin_table_actions.php";
require_once __DIR__."/file_upload.php";
?>
<!DOCTYPE html>
<html lang = "en">
	<head >
		<meta charset = "utf-8">
		<meta name = "viewport" content = "width=device-width, initial-scale=1">
		<title >Manage Catalog | Boardgame Hub</title >
		<link rel = "icon" href = "img/logo/logo.ico">
		<link rel = "stylesheet" href = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
		<link
			rel = "stylesheet"
			href = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		>
		<link rel = "stylesheet" href = "style/admin_theme.css?v=<?= filemtime(__DIR__."/style/admin_theme.css") ?>">
		<link rel = "stylesheet" href = "style/admin_pagination.css?v=2">
		<link
			rel = "stylesheet"
			href = "style/manage_catalog.css?v=<?= filemtime(__DIR__."/style/manage_catalog.css") ?>"
		>
		<link rel = "stylesheet" href = "style/file_upload.css">
	</head >
	<body >
	<?php include __DIR__."/admin_nav.php"; ?>
	<main class = "container py-5 admin-form-gap">
		<h1 >Manage Catalog</h1 >
		<p >
			<a class = "btn admin-back-button" href = "main_menu_admin.php">Back to Admin Menu</a >
		</p >
		<?php if ( $error ): ?>
			<div class = "alert alert-danger"><?= adminEscape($error) ?></div ><?php endif; ?>
		<?php if ( $notice ): ?>
			<div class = "alert alert-success"><?= adminEscape($notice) ?></div ><?php endif; ?>
		<?php adminDisclosureStart(
			$edit ? 'Edit Catalog' : 'Create Catalog', 'Enter board game details', (bool)($edit || $error)
		); ?>
		<form method = "post" enctype = "multipart/form-data" class = "admin-disclosure-content">
			<input type = "hidden" name = "token" value = "<?= adminEscape(adminToken()) ?>">
			<input type = "hidden" name = "action" value = "<?= $edit ? 'edit' : 'create' ?>">
			<input type = "hidden" name = "id" value = "<?= (int)($edit['id'] ?? 0) ?>">
			<div class = "admin-post-form-section">
				<div class = "row g-3">
					<div class = "col-md-4">
						<label class = "form-label" for = "catalog-name">Name</label >
						<input
							class = "form-control"
							id = "catalog-name"
							name = "name"
							maxlength = "100"
							required
							value = "<?= adminEscape($form['name'] ?? '') ?>"
						>
					</div >
					<?php foreach ( [ 'players' => 'Players', 'duration' => 'Duration (minutes)' ] as $field => $label )
						:
						$currentValue = (string)($form[$field] ?? '');
						$numericValue = preg_match(
							'/^([1-9][0-9]*) (?:Players|Minutes)$/', $currentValue, $matches
						) ? $matches[1] : '';
						if ( isset($_POST[$field]) ) $numericValue = (string)$_POST[$field];
						?>
						<div class = "col-md-4">
							<label class = "form-label" for = "catalog-<?= $field ?>"><?= $label ?></label >
							<input
								class = "form-control"
								id = "catalog-<?= $field ?>"
								name = "<?= $field ?>"
								type = "number"
								min = "1"
								step = "1"
								required
								value = "<?= adminEscape($numericValue) ?>"
							>
							<?php if ( $numericValue === '' && $currentValue !== '' ): ?>
								<small class = "form-text">Current: <?= adminEscape(
										$currentValue
									) ?>. Enter one number to replace it.
								</small >
							<?php endif; ?>
						</div >
					<?php endforeach; ?>
					<div class = "col-12">
						<label class = "form-label" for = "catalog-description">Description</label >
						<textarea
							class = "form-control"
							id = "catalog-description"
							name = "description"
							rows = "4"
							required
						><?= adminEscape($form['description'] ?? '') ?></textarea >
					</div >
					<fieldset class = "col-12">
						<legend class = "form-label fs-6">Categories (select one or more)</legend >
						<?php foreach ( $catalogCategories as $index => $category ): ?>
							<div class = "form-check form-check-inline">
								<input
									class = "form-check-input"
									type = "checkbox"
									id = "category-<?= $index ?>"
									name = "categories[]"
									value = "<?= adminEscape($category) ?>" <?= is_array(
									$selectedCategories
								) && in_array($category, $selectedCategories, true) ? 'checked' : '' ?>>
								<label class = "form-check-label" for = "category-<?= $index ?>"><?= adminEscape(
										$category
									) ?></label >
							</div >
						<?php endforeach; ?>
					</fieldset >
					<div class = "col-12">
						<?php renderFileUpload(
							[ 'id' => 'catalog-image', 'name' => 'image', 'label' => 'Catalog Image', 'kind' => 'image', 'accept' => '.jpg,.jpeg,.png,.webp', 'types' => 'JPG, PNG or WebP', 'max_size' => 'maximum 2 MB', 'current_url' => !empty($edit['has_image_data']) ? 'catalog_image.php?id='.(int)$edit['id'] : ($edit['image'] ?? '') ]
						); ?>
						<p class = "form-text">Optional. Leave unchanged to keep the current image.</p >
					</div >
				</div >
			</div >
			<button
				class = "btn admin-action mt-3"
				type = "submit"
			><?= $edit ? 'Save Changes' : 'Create Catalog' ?></button >
			<?php if ( $edit ): ?>
				<a class = "btn btn-outline-secondary mt-3" href = "manage_catalog.php">Cancel</a ><?php endif; ?>
		</form >
		<?php adminDisclosureEnd(); ?>
		<section class = "admin-panel">
			<h2 class = "h4">Manage Catalog</h2 >
			<?php if ( !$games ): ?>
				<p class = "mb-0">No catalog entries yet.</p ><?php else: ?>
				<div class = "table-responsive">
					<table class = "table table-striped align-middle">
						<thead >
						<tr >
							<th >ID</th >
							<th >Name</th >
							<th >Categories</th >
							<th >Players</th >
							<th >Duration</th >
							<th >Status</th >
							<th >Actions</th >
						</tr >
						</thead >
						<tbody data-admin-pagination = "catalog-pagination" data-admin-page-size = "5">
						<?php foreach ( $games as $game ): ?>
							<tr >
								<td ><?= (int)$game['id'] ?></td >
								<td >
									<strong ><?= adminEscape($game['name']) ?></strong >
								</td >
								<td ><?= adminEscape(
										implode(', ', json_decode($game['categories'], true) ?? [])
									) ?></td >
								<td ><?= adminEscape($game['players']) ?></td >
								<td ><?= adminEscape($game['duration']) ?></td >
								<td ><?= adminEscape(ucfirst($game['status'])) ?></td >
								<td >
									<div class = "admin-actions admin-post-actions"><?php adminTableActions(
											'edit_delete',
											[ 'edit_url' => 'manage_catalog.php?edit='.$game['id'], 'confirm' => 'Delete this catalog entry?', 'delete_action' => 'delete', 'id_field' => 'id', 'id' => $game['id'] ]
										); ?></div >
								</td >
							</tr >
						<?php endforeach; ?>
						</tbody >
					</table >
				</div >
				<nav class = "admin-pagination" aria-label = "Catalog pagination">
					<ul id = "catalog-pagination" class = "pagination justify-content-center mb-0"></ul >
				</nav >
			<?php endif; ?>
		</section >
	</main >
	<script src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script >
	<script src = "components/file_upload.js"></script >
	<script src = "components/pagination.js?v=<?= filemtime(__DIR__."/components/pagination.js") ?>"></script >
	<script
		src = "components/admin_pagination.js?v=<?= filemtime(
			__DIR__."/components/admin_pagination.js"
		) ?>"
	></script >
	</body >
</html >
