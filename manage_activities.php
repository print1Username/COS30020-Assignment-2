<?php $reviewKind = "activities";
require_once __DIR__."/manage_review_db.php"; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<link rel="icon" href="img/logo/logo.ico" sizes="any">
		<link rel="icon" type="image/png" href="img/logo/logo.png">
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Manage Activities | Boardgame Hub</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
		<link
			rel="stylesheet" href="style/admin_theme.css?v=<?php echo filemtime(__DIR__."/style/admin_theme.css"); ?>"
		>
		<link rel="stylesheet" href="style/admin_pagination.css?v=2">
	</head>
	<body><?php include __DIR__."/admin_nav.php"; ?>
	<main class="container py-5"><h1>Manage Activities and Registrations</h1>
		<p><a class="btn admin-back-button" href="main_menu_admin.php">Back to Admin Menu</a></p>
		<?php if ($error): ?>
			<div class="alert alert-danger"><?= adminEscape($error) ?></div><?php endif; ?><?php if ($notice): ?>
			<div class="alert alert-success"><?= adminEscape($notice) ?></div><?php endif; ?>
		<details class="admin-panel admin-disclosure mb-4" <?= $edit || $error ? 'open' : '' ?>>
			<summary class="admin-disclosure-header"><span><span
						class="admin-disclosure-title"
					><?= $edit ? 'Edit' : 'Add' ?> Registration</span><span class="admin-disclosure-subtitle">Enter participant and activity registration details</span></span>
			</summary>
			<form method="post" class="row g-3 admin-disclosure-content"><input
					type="hidden" name="token" value="<?= adminEscape(adminToken()) ?>"
				><input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>"><input
					type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>"
				>
				<?php if (!$edit): ?>
					<div class="col-12 admin-activity-picker" data-admin-activity-picker>
						<label class="form-label" for="activity-search">Activity</label>
						<div class="admin-activity-search-menu">
							<div class="input-group admin-activity-search-group">
								<input
									class="form-control" id="activity-search" type="search"
									placeholder="Search activities by name" autocomplete="off" required
									aria-controls="admin-activity-results" aria-expanded="false"
								>
								<button
									type="button" class="btn admin-activity-toggle" aria-label="Show activity options"
									aria-controls="admin-activity-results" aria-expanded="false"
								><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
							</div>
							<div
								class="admin-activity-results" id="admin-activity-results"
								aria-label="Matching activities" hidden
							>
								<?php foreach ($events as $event): ?><?php if ($event['status'] === 'approved'): ?>
									<button
										type="button" class="admin-activity-result"
										data-activity-id="<?= (int)$event['id'] ?>"
										data-activity-title="<?= adminEscape($event['title']) ?>"
										data-activity-category="<?= adminEscape($event['activity_type']) ?>"
										data-activity-date="<?= adminEscape($event['date']) ?>"
										data-activity-time="<?= adminEscape($event['time']) ?>"
										data-activity-end-time="<?= adminEscape($event['end_time']) ?>"
										data-activity-venue="<?= adminEscape($event['venue']) ?>"
										data-activity-table="<?= adminEscape($event['table_name']) ?>"
										data-activity-price="<?= adminEscape($event['price']) ?>"
										data-activity-description="<?= adminEscape($event['description']) ?>"
									><?= adminEscape($event['title']) ?></button>
								<?php endif; ?><?php endforeach; ?>
								<p class="admin-activity-no-results" hidden>No matching activities.</p>
							</div>
						</div>
						<input type="hidden" name="activity_id" value="<?= (int)($_POST['activity_id'] ?? 0) ?>">
						<div class="admin-activity-details" hidden aria-live="polite"></div>
					</div>
				<?php endif; ?>
				<?php
				foreach ($edit ? ['first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email', 'contact_number' => 'Contact Number', 'date' => 'Date', 'time' => 'Time'] : ['first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email', 'contact_number' => 'Contact Number']
				         as $field => $label): ?>
					<div class="col-md-4"><label class="form-label" for="<?= $field ?>"><?= $label ?></label><input
						class="form-control" id="<?= $field ?>" name="<?= $field ?>"
						type="<?= in_array($field, ['email', 'date', 'time'], true) ? $field : 'text' ?>"
						value="<?= adminEscape($edit[$field] ?? ($_POST[$field] ?? '')) ?>" <?= in_array($field,
						['email', 'first_name', 'last_name', 'contact_number'], true) ? 'required' : '' ?>></div><?php
				endforeach; ?><?php if ($edit): ?>
					<div class="col-md-4"><label class="form-label" for="status">Status</label><select
						class="form-select" id="status" name="status"
					><?php foreach (['pending', 'approved', 'rejected'] as $status): ?>
							<option
							value="<?= $status ?>" <?= ($edit['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php
						endforeach; ?></select></div><?php endif; ?>
				<div class="col-12">
					<button class="btn btn-primary">Save Registration</button> <?php if ($edit): ?><a
						class="btn btn-outline-secondary" href="manage_activities.php"
					>Cancel</a><?php endif; ?></div>
			</form>
		</details>
		<?php
		$pendingRows = array_values(array_filter($rows, fn($row) => $row['status'] === 'pending'));
		$reviewedRows = array_values(array_filter($rows,
			fn($row) => in_array($row['status'], ['approved', 'rejected'], true)));
		?>
		<section class="admin-panel mb-4">
			<h2 class="h4">Activity Registrations</h2>
			<?php foreach ([
							   ['Pending', $pendingRows, 'pending-registrations-pagination', 'No pending activity registrations are available.'],
							   ['Approved / Rejected', $reviewedRows, 'reviewed-registrations-pagination', 'No reviewed activity registrations are available.']
						   ] as [$heading, $registrationRows, $paginationId, $emptyMessage]): ?>
				<?php if ($heading === 'Approved / Rejected'): ?>
					<hr class="admin-registration-divider"><?php endif; ?>
				<h3 class="h5"><?= $heading ?></h3>
				<?php if (!$registrationRows): ?>
					<p class="mb-0 admin-registration-empty"><?= $emptyMessage ?></p>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-striped align-middle">
							<thead>
							<tr>
								<th>ID</th>
								<th>Activity</th>
								<th>Email</th>
								<th>Name</th>
								<th>Date / Time</th>
								<th>Contact</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
							</thead>
							<tbody data-admin-pagination="<?= $paginationId ?>" data-admin-page-size="4">
							<?php foreach ($registrationRows as $row): ?>
								<tr>
									<td><?= (int)$row['id'] ?></td>
									<td><?= adminEscape($row['activity_title'] ?? 'Not linked to an activity') ?></td>
									<td><?= adminEscape($row['email']) ?></td>
									<td><?= adminEscape($row['first_name'].' '.$row['last_name']) ?></td>
									<td><?= adminEscape(adminFormatDate($row['date']).' '.adminFormatTime($row['time'])) ?></td>
									<td><?= adminEscape($row['contact_number']) ?></td>
									<td><?= adminEscape($row['status']) ?></td>
									<td>
										<div class="admin-actions">
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
															aria-label="<?= $label ?> registration <?= (int)$row['id'] ?>"
														><i class="bi <?= $icon ?>" aria-hidden="true"></i></button>
													</form>
												<?php endforeach; ?>
											<?php endif; ?>
											<a
												class="btn btn-sm btn-outline-primary<?= $row['status'] === 'pending' ? ' ms-2' : '' ?>"
												href="manage_activities.php?edit=<?= (int)$row['id'] ?>"
											>Edit</a>
											<form method="post" onsubmit="return confirm('Delete this registration?')">
												<input
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
					<nav class="admin-pagination" aria-label="<?= $heading ?> registrations pagination">
						<ul id="<?= $paginationId ?>" class="pagination justify-content-center mb-0"></ul>
					</nav>
				<?php endif; ?>
			<?php endforeach; ?>
		</section>
		<section class="admin-panel"><h2 class="h4">Activities</h2>
			<?php if (!$events): ?><p class="mb-0">No activities are available.</p><?php else: ?>
				<div class="table-responsive">
					<table class="table table-striped align-middle">
						<thead>
						<tr>
							<th>ID</th>
							<th>Activity</th>
							<th>Category</th>
							<th>Date</th>
							<th>Time</th>
							<th>Venue</th>
							<th>Table</th>
							<th>Status</th>
						</tr>
						</thead>
						<tbody data-admin-pagination="events-pagination">
						<?php foreach ($events as $event): ?>
							<tr>
							<td><?= (int)$event['id'] ?></td>
							<td><strong><?= adminEscape($event['title']) ?></strong><?php if ($event['description']): ?>
									<div
										class="text-secondary small"
									><?= adminEscape($event['description']) ?></div><?php endif; ?></td>
							<td><?= adminEscape($event['activity_type']) ?></td>
							<td><?= adminEscape(adminFormatDate($event['date'])) ?></td>
							<td><?= adminEscape(adminFormatTime($event['time'])) ?><?php if ($event['end_time']): ?> - <?= adminEscape(adminFormatTime($event['end_time'])) ?><?php endif; ?></td>
							<td><?= adminEscape($event['venue']) ?></td>
							<td><?= adminEscape($event['table_name']) ?></td>
							<td><?= adminEscape($event['status']) ?></td></tr><?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<nav class="admin-pagination" aria-label="Activities pagination">
					<ul id="events-pagination" class="pagination justify-content-center mb-0"></ul>
				</nav><?php endif; ?></section>
	</main>
	<script src="components/pagination.js"></script>
	<script src="components/admin_pagination.js"></script>
	<script
		src="components/admin_activity_picker.js?v=<?php echo filemtime(__DIR__.'/components/admin_activity_picker.js'); ?>"
	></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>
