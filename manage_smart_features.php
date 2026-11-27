<?php
require_once __DIR__."/manage_smart_features_db.php";
require_once __DIR__."/admin_table_actions.php";
$formValue = static fn(string $key): string =>
	is_string($form[$key] ?? null) ? $form[$key] : "";
?>
<!DOCTYPE html>
<html lang = "en">
	<head >
		<meta charset = "utf-8">
		<meta name = "viewport" content = "width=device-width, initial-scale=1">
		<title >Manage Smart Features | Boardgame Hub</title >
		<link rel = "icon" href = "img/logo/logo.ico">
		<link rel = "stylesheet" href = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
		<link
			rel = "stylesheet"
			href = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		>
		<link rel = "stylesheet" href = "style/admin_theme.css?v=<?= filemtime(__DIR__."/style/admin_theme.css") ?>">
		<link
			rel = "stylesheet"
			href = "style/manage_smart_features.css?v=<?= filemtime(__DIR__."/style/manage_smart_features.css") ?>"
		>
	</head >
	<body >
	<?php include __DIR__."/admin_nav.php"; ?>
	<main class = "container py-5 admin-form-gap">
		<h1 >Manage Smart Features</h1 >
		<p >
			<a class = "btn admin-back-button" href = "main_menu_admin.php">Back to Admin Menu</a >
		</p >
		<?php if ( $error !== "" ): ?>
			<div class = "alert alert-danger" role = "alert"><?= adminEscape($error) ?></div >
		<?php endif; ?>
		<?php if ( $notice !== "" ): ?>
			<div class = "alert alert-success" role = "status"><?= adminEscape($notice) ?></div >
		<?php endif; ?>

		<nav class = "nav nav-pills smart-feature-review-filters" aria-label = "Smart Feature status filters">
			<?php foreach ( $smartFeatureStatuses as $filterStatus ): ?>
				<a
					class = "nav-link<?= $status === $filterStatus ? ' active' : '' ?>"
					href = "manage_smart_features.php?status=<?= adminEscape($filterStatus) ?>"
					<?= $status === $filterStatus ? 'aria-current="page"' : '' ?>
				><?= adminEscape(ucfirst($filterStatus)) ?></a >
			<?php endforeach; ?>
		</nav >

		<?php if ( $showEditForm && $submission !== null ): ?>
			<section class = "admin-panel smart-feature-edit-panel">
				<h2 class = "h4">Edit submission #<?= (int)$submission["id"] ?></h2 >
				<form method = "post" enctype = "multipart/form-data">
					<input type = "hidden" name = "token" value = "<?= adminEscape(adminToken()) ?>">
					<input type = "hidden" name = "action" value = "edit">
					<input type = "hidden" name = "id" value = "<?= (int)$submission["id"] ?>">
					<input type = "hidden" name = "status_filter" value = "<?= adminEscape($status) ?>">
					<div class = "row g-3">
						<div class = "col-md-6">
							<label class = "form-label" for = "smart-feature-name">Game name</label >
							<input
								class = "form-control"
								id = "smart-feature-name"
								name = "item_name"
								maxlength = "50"
								value = "<?= adminEscape($formValue("item_name")) ?>"
								required
							>
						</div >
						<div class = "col-md-6">
							<label class = "form-label" for = "smart-feature-category">Category</label >
							<input
								class = "form-control"
								id = "smart-feature-category"
								name = "item_category"
								maxlength = "50"
								value = "<?= adminEscape($formValue("item_category")) ?>"
								required
							>
						</div >
						<div class = "col-12">
							<label class = "form-label" for = "smart-feature-description">Description</label >
							<textarea
								class = "form-control"
								id = "smart-feature-description"
								name = "item_description"
								rows = "4"
								maxlength = "65535"
							><?= adminEscape($formValue("item_description")) ?></textarea >
						</div >
						<div class = "col-md-6">
							<label class = "form-label" for = "smart-feature-players">Players</label >
							<input
								class = "form-control"
								id = "smart-feature-players"
								name = "players"
								maxlength = "50"
								value = "<?= adminEscape($formValue("players")) ?>"
								placeholder = "e.g. 2-4 players"
							>
						</div >
						<div class = "col-md-6">
							<label class = "form-label" for = "smart-feature-duration">Duration</label >
							<input
								class = "form-control"
								id = "smart-feature-duration"
								name = "duration"
								maxlength = "50"
								value = "<?= adminEscape($formValue("duration")) ?>"
								placeholder = "e.g. 30-60 minutes"
							>
						</div >
						<div class = "col-md-6">
							<label class = "form-label" for = "smart-feature-image">Replace image (optional)</label >
							<input
								class = "form-control"
								type = "file"
								id = "smart-feature-image"
								name = "item_image"
								accept = "image/jpeg,image/png"
							>
							<?php $imagePreview = smartFeatureAdminAsset($submission["item_image"], "image"); ?>
							<?php if ( $imagePreview !== null ): ?>
								<p class = "smart-feature-current-file">
									Current image:
									<a href = "<?= adminEscape($imagePreview) ?>" target = "_blank" rel = "noopener">
										<?= adminEscape(basename($imagePreview)) ?>
									</a >
								</p >
							<?php else: ?>
								<p class = "smart-feature-current-file">No image currently available.</p >
							<?php endif; ?>
						</div >
						<div class = "col-md-6">
							<label class = "form-label" for = "smart-feature-pdf">Replace PDF (optional)</label >
							<input
								class = "form-control"
								type = "file"
								id = "smart-feature-pdf"
								name = "description_file"
								accept = "application/pdf"
							>
							<?php $pdfPreview = smartFeatureAdminAsset($submission["description_file"], "pdf"); ?>
							<?php if ( $pdfPreview !== null ): ?>
								<p class = "smart-feature-current-file">
									Current PDF:
									<a href = "<?= adminEscape($pdfPreview) ?>" target = "_blank" rel = "noopener">
										<?= adminEscape(basename($pdfPreview)) ?>
									</a >
								</p >
							<?php else: ?>
								<p class = "smart-feature-current-file">No PDF currently available.</p >
							<?php endif; ?>
						</div >
					</div >
					<div class = "smart-feature-edit-actions">
						<button class = "btn admin-action" type = "submit">Save Changes</button >
						<a
							class = "btn btn-outline-secondary"
							href = "manage_smart_features.php?status=<?= adminEscape($status) ?>"
						>Cancel
						</a >
					</div >
				</form >
			</section >
		<?php endif; ?>

		<section class = "smart-feature-submissions" aria-labelledby = "smart-feature-submissions-heading">
			<h2 id = "smart-feature-submissions-heading" class = "h4"><?= adminEscape(ucfirst($status)) ?> submissions</h2 >
			<?php if ( !$smartFeatureSubmissions ): ?>
				<p class = "admin-panel mb-0">No <?= adminEscape($status) ?> Smart Feature submissions.</p >
			<?php else: ?>
				<div class = "smart-feature-submission-list">
					<?php foreach ( $smartFeatureSubmissions as $entry ): ?>
						<?php
						$image = smartFeatureAdminAsset($entry["item_image"], "image");
						$pdf = smartFeatureAdminAsset($entry["description_file"], "pdf");
						$badgeClass = match ( $entry["status"] ) {
							"approved" => "text-bg-success",
							"rejected" => "text-bg-danger",
							default => "text-bg-warning"
						};
						?>
						<article class = "admin-panel smart-feature-submission-card">
							<div class = "row g-4">
								<div class = "col-lg-7">
									<div class = "smart-feature-submission-heading">
										<div>
											<p class = "smart-feature-submission-id">Submission #<?= (int)$entry["id"] ?></p >
											<h3 class = "h5 mb-1"><?= adminEscape($entry["item_name"]) ?></h3 >
										</div >
										<span class = "badge <?= $badgeClass ?>"><?= adminEscape(ucfirst($entry["status"])) ?></span >
									</div >
									<p class = "smart-feature-submission-category">
										<strong>Category:</strong> <?= adminEscape($entry["item_category"]) ?>
									</p >
									<p class = "smart-feature-submission-description">
										<?= $entry["item_description"] !== null && $entry["item_description"] !== ""
											? nl2br(adminEscape($entry["item_description"]))
											: '<span class="text-secondary">No description provided.</span>' ?>
									</p >
									<p class = "mb-0 text-secondary">
										<strong>Players:</strong> <?= adminEscape($entry["players"] ?: "Not specified") ?>
										<span class = "mx-2" aria-hidden = "true">·</span>
										<strong>Duration:</strong> <?= adminEscape($entry["duration"] ?: "Not specified") ?>
									</p >
								</div >
								<div class = "col-lg-5">
									<div class = "smart-feature-submission-previews">
										<div>
											<h4 class = "smart-feature-preview-title">Image</h4 >
											<?php if ( $image !== null ): ?>
												<a href = "<?= adminEscape($image) ?>" target = "_blank" rel = "noopener">
													<img
														class = "smart-feature-preview-image"
														src = "<?= adminEscape($image) ?>"
														alt = "<?= adminEscape($entry["item_name"]) ?> image preview"
													>
												</a >
											<?php else: ?>
												<p class = "text-secondary mb-0">No image available.</p >
											<?php endif; ?>
										</div >
										<div>
											<h4 class = "smart-feature-preview-title">PDF description</h4 >
											<?php if ( $pdf !== null ): ?>
												<details class = "smart-feature-pdf-preview">
													<summary>Preview PDF</summary >
													<object
														data = "<?= adminEscape($pdf) ?>"
														type = "application/pdf"
														aria-label = "PDF description for <?= adminEscape($entry["item_name"]) ?>"
													>
														<p >
															Preview unavailable.
															<a href = "<?= adminEscape($pdf) ?>" target = "_blank" rel = "noopener">Open PDF</a >
														</p >
													</object >
												</details >
												<a href = "<?= adminEscape($pdf) ?>" target = "_blank" rel = "noopener">Open PDF in new tab</a >
											<?php else: ?>
												<p class = "text-secondary mb-0">No PDF available.</p >
											<?php endif; ?>
										</div >
									</div >
								</div >
								<div class = "col-12">
									<div class = "smart-feature-submission-actions">
										<a
											class = "btn btn-sm btn-outline-primary"
											href = "manage_smart_features.php?status=<?= adminEscape($status) ?>&amp;edit=<?= (int)$entry["id"] ?>"
										>Edit
										</a >
										<?php adminTableActions(
											"review",
											[ "id" => $entry["id"], "status" => $entry["status"], "subject" => $entry["item_name"] ]
										); ?>
										<?php adminTableActions(
											"delete_only",
											[
												"confirm" => "Delete this user-submitted Smart Feature?",
												"delete_action" => "delete",
												"id_field" => "id",
												"id" => $entry["id"]
											]
										); ?>
									</div >
								</div >
							</div >
						</article >
					<?php endforeach; ?>
				</div >
			<?php endif; ?>
		</section >
	</main >
	</body >
</html >
