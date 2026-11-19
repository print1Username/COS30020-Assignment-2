<?php $reviewKind = "community";
require_once __DIR__."/manage_review_db.php"; ?>
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
					><?= $edit ? 'Edit' : 'Add' ?> Post</span><span class="admin-disclosure-subtitle">Enter author, community and post details</span></span>
			</summary>
			<form method="post" class="row g-3 admin-disclosure-content"><input
					type="hidden" name="token" value="<?= adminEscape(adminToken()) ?>"
				><input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>"><input
					type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>"
				>
				<div class="col-md-6"><label class="form-label" for="community_id">Community</label><select
						class="form-select" id="community_id" name="community_id" required
					>
						<option value="">Select a community</option><?php foreach ($communities as $community): ?>
							<option
								value="<?= (int)$community['id'] ?>" <?= (int)($edit['community_id'] ?? ($_POST['community_id'] ?? 0)) === (int)$community['id'] ? 'selected' : '' ?>><?= adminEscape($community['title']) ?></option>
						<?php endforeach; ?></select></div>
				<?php foreach (['first_name' => 'First Name', 'last_name' => 'Last Name'] as $field => $label): ?>
					<div class="col-md-3"><label class="form-label" for="<?= $field ?>"><?= $label ?></label><input
							class="form-control" id="<?= $field ?>" name="<?= $field ?>" maxlength="50" required
							value="<?= adminEscape($edit[$field] ?? ($_POST[$field] ?? '')) ?>"
						></div>
				<?php endforeach; ?>
				<div class="col-md-6"><label class="form-label" for="email">Author Email <span
							class="text-muted"
						>(optional)</span></label><input
						class="form-control" id="email" name="email" type="email" maxlength="50"
						value="<?= adminEscape($edit['email'] ?? ($_POST['email'] ?? '')) ?>"
					></div>
				<div class="col-md-6"><label class="form-label" for="title">Title</label><input
						class="form-control" id="title" name="title" maxlength="100" required
						value="<?= adminEscape($edit['title'] ?? ($_POST['title'] ?? '')) ?>"
					></div>
				<div class="col-md-8"><label class="form-label" for="contribution_image">Image Path</label><input
						class="form-control" id="contribution_image" name="contribution_image" maxlength="100"
						value="<?= adminEscape($edit['contribution_image'] ?? ($_POST['contribution_image'] ?? '')) ?>"
					></div>
				<div class="col-md-4"><label class="form-label" for="status">Status</label><select
						class="form-select" id="status" name="status"
					><?php foreach (['pending', 'approved', 'rejected'] as $status): ?>
							<option
							value="<?= $status ?>" <?= ($edit['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php
						endforeach; ?></select></div>
				<div class="col-12"><label
						class="form-label" for="description"
					>Description</label><textarea
						class="form-control" id="description" name="description" rows="4" required
					><?= adminEscape($edit['description'] ?? ($_POST['description'] ?? '')) ?></textarea></div>
				<div class="col-12">
					<button class="btn btn-primary">Save Post</button> <?php if ($edit): ?><a
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
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>
