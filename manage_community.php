<?php $reviewKind = "community";
require_once __DIR__."/manage_review_db.php";
require_once __DIR__."/file_upload.php"; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<link rel="icon" href="public/logo.ico" sizes="any">
		<link rel="icon" type="image/png" href="public/logo.png">
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Manage Community | Boardgame Hub</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
		<link
			rel="stylesheet" href="style/admin_theme.css?v=<?php echo filemtime(__DIR__."/style/admin_theme.css"); ?>"
		>
		<link rel="stylesheet" href="style/admin_pagination.css?v=2">
		<link
			rel="stylesheet" href="style/file_upload.css?v=<?php echo filemtime(__DIR__."/style/file_upload.css"); ?>"
		>
	</head>
	<body><?php include __DIR__."/admin_nav.php"; ?>
	<main class="container py-5"><h1>Manage Community Posts</h1>
		<p><a class="btn admin-back-button" href="main_menu_admin.php">Back to Admin Menu</a></p>
		<?php if ($error): ?>
			<div class="alert alert-danger"><?= adminEscape($error) ?></div><?php endif; ?><?php if ($notice): ?>
			<div class="alert alert-success"><?= adminEscape($notice) ?></div><?php endif; ?>
		<details class="admin-panel admin-disclosure mb-4" <?= $edit || $error ? 'open' : '' ?>>
			<summary class="admin-disclosure-header"><span><span
						class="admin-disclosure-title"
					><?= $edit ? 'Edit Post' : 'Create Post' ?></span><span class="admin-disclosure-subtitle">Enter author and post content details</span></span>
			</summary>
			<form method="post" enctype="multipart/form-data" class="admin-disclosure-content"><input
					type="hidden" name="token" value="<?= adminEscape(adminToken()) ?>"
				><input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>"><input
					type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>"
				>
				<div class="admin-post-form-grid">
					<section class="admin-post-form-section" aria-labelledby="post-user-title">
						<h3 id="post-user-title">User Details</h3>
						<?php foreach (['first_name' => 'First Name', 'last_name' => 'Last Name'] as $field => $label):
							?>
							<div><label class="form-label" for="<?= $field ?>"><?= $label ?></label><input
									class="form-control" id="<?= $field ?>" name="<?= $field ?>" maxlength="50" required
									value="<?= adminEscape($edit[$field] ?? ($_POST[$field] ?? $adminUser[$field])) ?>"
								></div>
						<?php endforeach; ?>
						<div><label class="form-label" for="email">Email <span
									class="text-muted"
								>(optional)</span></label><input
								class="form-control" id="email" name="email" type="email" maxlength="50"
								value="<?= adminEscape($edit['email'] ?? ($_POST['email'] ?? $adminUser['email'])) ?>"
							></div>
					</section>
					<section class="admin-post-form-section" aria-labelledby="post-content-title">
						<h3 id="post-content-title">Post Content</h3>
						<div class="admin-community-picker" data-admin-community-picker>
							<label class="form-label" for="community-search">Community</label>
							<div class="admin-activity-search-menu">
								<div class="input-group admin-activity-search-group">
									<input
										class="form-control" id="community-search" type="search"
										placeholder="Search communities by name" autocomplete="off" required
										aria-controls="admin-community-results" aria-expanded="false"
									>
									<button
										type="button" class="btn admin-activity-toggle"
										aria-label="Show community options" aria-controls="admin-community-results"
										aria-expanded="false"
									><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
								</div>
								<input
									type="hidden" name="community_id"
									value="<?= (int)($edit['community_id'] ?? ($_POST['community_id'] ?? 0)) ?>"
								>
								<div
									class="admin-activity-results" id="admin-community-results"
									aria-label="Matching communities" hidden
								>
									<?php foreach ($communities as $community): ?>
										<button type="button" class="admin-activity-result admin-community-result"
												data-community-id="<?= (int)$community['id'] ?>"
												data-community-title="<?= adminEscape($community['title']) ?>"><?= adminEscape($community['title']) ?></button><?php
									endforeach; ?>
									<p class="admin-activity-no-results text-muted" hidden>No matching communities.</p>
								</div>
							</div>
						</div>
						<div><label class="form-label" for="title">Title</label><input
								class="form-control" id="title" name="title" maxlength="100" required
								value="<?= adminEscape($edit['title'] ?? ($_POST['title'] ?? '')) ?>"
							></div>
						<div><label class="form-label" for="description">Description</label><textarea
								class="form-control" id="description" name="description" rows="5" required
							><?= adminEscape($edit['description'] ?? ($_POST['description'] ?? '')) ?></textarea></div>
						<div><?php
							$currentAdminPostImage = $edit && !empty($edit["contribution_image"]) &&
							is_file(__DIR__."/".$edit["contribution_image"])
								? $edit["contribution_image"] : "";
							renderFileUpload([
								"id" => "contribution_image",
								"name" => "contribution_image",
								"label" => "Post Image",
								"accept" => "image/jpeg,image/png",
								"kind" => "image",
								"types" => "JPG, JPEG and PNG images only",
								"max_size" => "maximum 5MB",
								"current_url" => $currentAdminPostImage,
								"current_name" => $currentAdminPostImage !== "" ? basename($currentAdminPostImage) : ""
							]);
							?></div>
						<?php if ($edit): ?>
							<div class="admin-post-status"><label class="form-label" for="status">Status</label><select
								class="form-select" id="status" name="status"
							><?php foreach (['pending', 'approved', 'rejected'] as $status): ?>
									<option
									value="<?= $status ?>" <?= ($edit['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php
								endforeach; ?></select></div><?php endif; ?>
					</section>
				</div>
				<div class="admin-post-form-actions">
					<button
						class="btn btn-primary"
					><?= $edit ? 'Save Post' : 'Create Post' ?></button><?php if ($edit): ?><a
						class="btn btn-outline-secondary" href="manage_community.php"
					>Cancel</a><?php endif; ?></div>
			</form>
		</details>
		<?php
		$pendingRows = array_values(array_filter($rows, fn($row) => $row['status'] === 'pending'));
		$reviewedRows = array_values(array_filter($rows,
			fn($row) => in_array($row['status'], ['approved', 'rejected'], true)));
		?>
		<section class="admin-panel"><h2 class="h4">Community Posts</h2>
			<?php foreach ([
							   ['Pending', $pendingRows, 'pending-posts-pagination', 'No pending community posts are available.'],
							   ['Approved / Rejected', $reviewedRows, 'reviewed-posts-pagination', 'No reviewed community posts are available.']
						   ] as [$heading, $postRows, $paginationId, $emptyMessage]): ?>
				<?php if ($heading === 'Approved / Rejected'): ?>
					<hr class="admin-registration-divider"><?php endif; ?>
				<h3 class="h5"><?= $heading ?></h3>
				<?php if (!$postRows): ?>
					<p class="mb-0 admin-registration-empty"><?= $emptyMessage ?></p>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-striped align-middle admin-post-table">
							<thead>
							<tr>
								<th>ID</th>
								<th>Community</th>
								<th>Author</th>
								<th>Title</th>
								<th>Description</th>
								<th>Image</th>
								<th>Status</th>
								<th>Posted At</th>
								<th>Actions</th>
							</tr>
							</thead>
							<tbody data-admin-pagination="<?= $paginationId ?>" data-admin-page-size="4">
							<?php foreach ($postRows as $row): ?>
								<tr>
									<td><?= (int)$row['id'] ?></td>
									<td><?= adminEscape($row['community_title'] ?? 'Unknown community') ?></td>
									<td><?= adminEscape(trim($row['first_name'].' '.$row['last_name'])) ?><?php if ($row['email']): ?>
											<br><small
												class="text-muted"
											><?= adminEscape($row['email']) ?></small><?php endif; ?></td>
									<td><strong><?= adminEscape($row['title']) ?></strong></td>
									<td class="admin-post-description"><?= adminEscape($row['description']) ?></td>
									<td><?php if ($row['contribution_image']): ?><?php if (is_file(__DIR__.'/'.$row['contribution_image'])): ?>
											<img class="admin-post-thumbnail"
												 src="<?= adminEscape($row['contribution_image']) ?>"
												 alt="Post image"><?php else: ?>
											<small><?= adminEscape($row['contribution_image']) ?></small><?php endif; ?><?php else: ?>&mdash;<?php endif; ?>
									</td>
									<td><?= adminEscape(ucfirst($row['status'])) ?></td>
									<td><?= adminEscape($row['created_at']) ?></td>
									<td>
										<div class="admin-actions admin-post-actions">
											<?php if ($row['status'] === 'pending'): ?>
												<?php
												foreach (['approved' => ['Approve', 'bi-check-lg', 'btn-success'], 'rejected' => ['Reject', 'bi-x-lg', 'btn-danger']]
												         as $action => [$label, $icon, $buttonClass]): ?>
													<form method="post"><input
															type="hidden" name="token"
															value="<?= adminEscape(adminToken()) ?>"
														><input
															type="hidden" name="action" value="<?= $action ?>"
														><input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
														<button
															type="submit" class="btn btn-sm <?= $buttonClass ?>"
															title="<?= $label ?>"
															aria-label="<?= $label ?> post <?= (int)$row['id'] ?>"
														><i class="bi <?= $icon ?>" aria-hidden="true"></i></button>
													</form>
												<?php endforeach; ?>
											<?php endif; ?>
											<a
												class="btn btn-sm btn-outline-primary<?= $row['status'] === 'pending' ? ' ms-2' : '' ?>"
												href="manage_community.php?edit=<?= (int)$row['id'] ?>"
											>Edit</a>
											<form method="post" onsubmit="return confirm('Delete this post?')"><input
													type="hidden" name="token" value="<?= adminEscape(adminToken()) ?>"
												><input type="hidden" name="action" value="delete"><input
													type="hidden" name="id" value="<?= (int)$row['id'] ?>"
												>
												<button type="submit" class="btn btn-sm btn-outline-danger">Delete
												</button>
											</form>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<nav class="admin-pagination" aria-label="<?= $heading ?> posts pagination">
						<ul id="<?= $paginationId ?>" class="pagination justify-content-center mb-0"></ul>
					</nav>
				<?php endif; ?>
			<?php endforeach; ?>
		</section>
	</main>
	<script src="components/pagination.js"></script>
	<script src="components/admin_pagination.js"></script>
	<script
		src="components/admin_community_picker.js?v=<?php echo filemtime(__DIR__.'/components/admin_community_picker.js'); ?>"
	></script>
	<script src="components/file_upload.js?v=<?php echo filemtime(__DIR__."/components/file_upload.js"); ?>"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>
