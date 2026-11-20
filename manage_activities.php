<?php
$reviewKind = "activities";
require_once __DIR__."/manage_review_db.php";
require_once __DIR__."/manage_activity_info_db.php";
require_once __DIR__."/admin_disclosure.php";
require_once __DIR__."/admin_table_actions.php";
$activityTab = $_GET["tab"] ?? "activities";
if ( !in_array($activityTab, [ "activities", "registrations" ], true) ) $activityTab = "activities";
if ( $edit || $error ) $activityTab = "registrations";
if ( $activityEdit || $activityError ) $activityTab = "activities";
$activityPost = str_starts_with((string)($_POST["action"] ?? ""), "activity_") ? $_POST : [];
$activityForm = $activityEdit ?: $activityPost;
?>
<!DOCTYPE html>
<html lang = "en">
	<head >
		<link rel = "icon" href = "img/logo/logo.ico" sizes = "any">
		<link rel = "icon" type = "image/png" href = "img/logo/logo.png">
		<meta charset = "utf-8">
		<meta name = "viewport" content = "width=device-width, initial-scale=1">
		<title >Manage Activities | Boardgame Hub</title >
		<link href = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel = "stylesheet">
		<link
			rel = "stylesheet"
			href = "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		>
		<link rel = "stylesheet" href = "style/admin_theme.css?v=<?= filemtime(__DIR__."/style/admin_theme.css") ?>">
		<link rel = "stylesheet" href = "style/admin_pagination.css?v=2">
		<link
			rel = "stylesheet"
			href = "style/manage_activities.css?v=<?= filemtime(__DIR__."/style/manage_activities.css") ?>"
		>
	</head >
	<body ><?php include __DIR__."/admin_nav.php"; ?>
	<main class = "container py-5">
		<h1 >Manage Activities and Registrations</h1 >
		<p >
			<a class = "btn admin-back-button" href = "main_menu_admin.php">Back to Admin Menu</a >
		</p >
		<nav class = "admin-community-tabs admin-activity-tabs" aria-label = "Activity management sections">
			<a
				class = "admin-community-tab<?= $activityTab === 'activities' ? ' active' : '' ?>"
				href = "manage_activities.php?tab=activities" <?= $activityTab === 'activities' ? 'aria-current="page"' : '' ?>>Activities
			</a >
			<a
				class = "admin-community-tab<?= $activityTab === 'registrations' ? ' active' : '' ?>"
				href = "manage_activities.php?tab=registrations" <?= $activityTab === 'registrations' ? 'aria-current="page"' : '' ?>>Registrations
			</a >
		</nav >
		<?php if ( $error ): ?>
			<div class = "alert alert-danger"><?= adminEscape($error) ?></div ><?php endif; ?>
		<?php if ( $activityError ): ?>
			<div class = "alert alert-danger"><?= adminEscape($activityError) ?></div ><?php endif; ?>
		<?php if ( $notice ): ?>
			<div class = "alert alert-success"><?= adminEscape($notice) ?></div ><?php endif; ?>

		<div <?= $activityTab !== 'activities' ? 'hidden' : '' ?>>
			<?php adminDisclosureStart(
				$activityEdit ? 'Edit Activity' : 'Create Activity', 'Enter activity details',
				(bool)($activityEdit || $activityError)
			); ?>
			<form method = "post" class = "row g-3 admin-disclosure-content">
				<input type = "hidden" name = "token" value = "<?= adminEscape(adminToken()) ?>">
				<input
					type = "hidden"
					name = "action"
					value = "<?= $activityEdit ? 'activity_edit' : 'activity_add' ?>"
				>
				<input type = "hidden" name = "activity_id" value = "<?= (int)($activityEdit['id'] ?? 0) ?>">
				<div class = "col-md-6">
					<label class = "form-label" for = "activity-title">Title</label >
					<input
						class = "form-control"
						id = "activity-title"
						name = "activity_title"
						maxlength = "100"
						required
						value = "<?= adminEscape($activityForm['title'] ?? ($activityForm['activity_title'] ?? '')) ?>"
					>
				</div >
				<div class = "col-md-6">
					<label class = "form-label" for = "activity-type">Type</label >
					<input
						class = "form-control"
						id = "activity-type"
						name = "activity_type"
						maxlength = "50"
						value = "<?= adminEscape($activityForm['activity_type'] ?? '') ?>"
					>
				</div >
				<div class = "col-12">
					<label class = "form-label" for = "activity-description">Description</label >
					<textarea
						class = "form-control"
						id = "activity-description"
						name = "activity_description"
						rows = "3"
					><?= adminEscape(
							$activityForm['description'] ?? ($activityForm['activity_description'] ?? '')
						) ?></textarea >
				</div >
				<div class = "col-md-4">
					<label class = "form-label" for = "activity-date">Date</label >
					<input
						class = "form-control"
						id = "activity-date"
						name = "activity_date"
						type = "date"
						value = "<?= adminEscape($activityForm['date'] ?? ($activityForm['activity_date'] ?? '')) ?>"
					>
				</div >
				<div class = "col-md-4">
					<label class = "form-label" for = "activity-time">Start Time</label >
					<input
						class = "form-control"
						id = "activity-time"
						name = "activity_time"
						type = "time"
						value = "<?= adminEscape($activityForm['time'] ?? ($activityForm['activity_time'] ?? '')) ?>"
					>
				</div >
				<div class = "col-md-4">
					<label class = "form-label" for = "activity-end-time">End Time</label >
					<input
						class = "form-control"
						id = "activity-end-time"
						name = "activity_end_time"
						type = "time"
						value = "<?= adminEscape(
							$activityForm['end_time'] ?? ($activityForm['activity_end_time'] ?? '')
						) ?>"
					>
				</div >
				<div class = "col-md-4">
					<label class = "form-label" for = "activity-price">Price (RM)</label >
					<input
						class = "form-control"
						id = "activity-price"
						name = "activity_price"
						type = "number"
						min = "0"
						max = "999999.99"
						step = "0.01"
						value = "<?= adminEscape($activityForm['price'] ?? ($activityForm['activity_price'] ?? '')) ?>"
					>
				</div >
				<div class = "col-md-4">
					<label class = "form-label" for = "activity-venue">Venue</label >
					<input
						class = "form-control"
						id = "activity-venue"
						name = "activity_venue"
						maxlength = "100"
						value = "<?= adminEscape($activityForm['venue'] ?? ($activityForm['activity_venue'] ?? '')) ?>"
					>
				</div >
				<div class = "col-md-4">
					<label class = "form-label" for = "activity-table-name">Table</label >
					<input
						class = "form-control"
						id = "activity-table-name"
						name = "activity_table_name"
						maxlength = "50"
						value = "<?= adminEscape(
							$activityForm['table_name'] ?? ($activityForm['activity_table_name'] ?? '')
						) ?>"
					>
				</div >
				<div class = "col-md-8">
					<label class = "form-label" for = "activity-image">Image Path</label >
					<input
						class = "form-control"
						id = "activity-image"
						name = "activity_image"
						maxlength = "100"
						value = "<?= adminEscape($activityForm['image'] ?? ($activityForm['activity_image'] ?? '')) ?>"
					>
				</div >
				<div class = "col-md-4">
					<label class = "form-label" for = "activity-status">Status</label >
					<select class = "form-select" id = "activity-status" name = "activity_status">
						<?php $selectedActivityStatus = $activityForm['status'] ?? ($activityForm['activity_status'] ?? 'pending');
						foreach ( [ "pending", "approved", "rejected" ] as $status ): ?>
							<option value = "<?= $status ?>" <?= $selectedActivityStatus === $status ? 'selected' : '' ?>><?= ucfirst(
							$status
						) ?></option ><?php endforeach; ?>
					</select >
				</div >
				<div class = "col-12">
					<button
						class = "btn btn-primary"
						type = "submit"
					><?= $activityEdit ? 'Save Activity' : 'Create Activity' ?></button > <?php if ( $activityEdit ): ?>
						<a
							class = "btn btn-outline-secondary"
							href = "manage_activities.php?tab=activities"
						>Cancel
						</a ><?php endif; ?></div >
			</form ><?php adminDisclosureEnd(); ?>
			<section class = "admin-panel">
				<h2 class = "h4">Manage Activity</h2 >
				<?php if ( !$events ): ?>
					<p class = "mb-0">No activities are available.</p ><?php else: ?>
					<div class = "table-responsive">
					<table class = "table table-striped align-middle admin-activity-table">
						<thead >
						<tr >
							<th >ID</th >
							<th >Activity</th >
							<th >Type</th >
							<th >Date</th >
							<th >Time</th >
							<th >Price</th >
							<th >Venue</th >
							<th >Table</th >
							<th >Status</th >
							<th >Actions</th >
						</tr >
						</thead >
						<tbody data-admin-pagination = "activities-pagination" data-admin-page-size = "5"><?php
						foreach ( $events as $event ): ?>
							<tr >
							<td ><?= (int)$event['id'] ?></td >
							<td >
								<strong ><?= adminEscape(
										$event['title']
									) ?></strong ><?php if ( $event['description'] ): ?>
									<small class = "d-block text-muted admin-activity-description"
									title = "<?= adminEscape($event['description']) ?>"><?= adminEscape(
									$event['description']
								) ?></small ><?php endif; ?></td >
							<td ><?= adminEscape($event['activity_type']) ?></td >
							<td ><?= adminEscape(adminFormatDate($event['date'])) ?></td >
							<td ><?= adminEscape(
									adminFormatTime($event['time'])
								) ?><?php if ( $event['end_time'] ): ?> - <?= adminEscape(
									adminFormatTime($event['end_time'])
								) ?><?php endif; ?></td >
							<td ><?= $event['price'] !== null ? 'RM '.adminEscape($event['price']) : '' ?></td >
							<td ><?= adminEscape($event['venue']) ?></td >
							<td ><?= adminEscape($event['table_name']) ?></td >
							<td ><?= adminEscape(ucfirst($event['status'])) ?></td >
							<td >
								<div class = "admin-actions admin-post-actions">
									<?php adminTableActions(
										'edit_delete',
										[ 'id' => (int)$event['id'], 'edit_url' => 'manage_activities.php?activity_edit='.(int)$event['id'], 'confirm' => 'Delete this activity?', 'delete_action' => 'activity_delete', 'id_field' => 'activity_id' ]
									); ?>
								</div >
							</td ></tr ><?php endforeach; ?></tbody >
					</table ></div >
					<nav class = "admin-pagination" aria-label = "Activities pagination">
						<ul id = "activities-pagination" class = "pagination justify-content-center mb-0"></ul >
					</nav ><?php endif; ?>
			</section >
		</div >

		<div <?= $activityTab !== 'registrations' ? 'hidden' : '' ?>>
			<?php adminDisclosureStart(
				($edit ? 'Edit' : 'Add').' Registration', 'Enter participant and activity registration details',
				(bool)($edit || $error)
			); ?>
			<form method = "post" class = "row g-3 admin-disclosure-content">
				<input type = "hidden" name = "token" value = "<?= adminEscape(adminToken()) ?>">
				<input type = "hidden" name = "action" value = "<?= $edit ? 'edit' : 'add' ?>">
				<input type = "hidden" name = "id" value = "<?= (int)($edit['id'] ?? 0) ?>">
				<?php if ( !$edit ): ?>
					<div class = "col-12 admin-activity-picker" data-admin-activity-picker>
					<label class = "form-label" for = "activity-search">Activity</label >
					<div class = "admin-activity-search-menu">
						<div class = "input-group admin-activity-search-group">
							<input
								class = "form-control"
								id = "activity-search"
								type = "search"
								placeholder = "Search activities by name"
								autocomplete = "off"
								required
								aria-controls = "admin-activity-results"
								aria-expanded = "false"
							>
							<button
								type = "button"
								class = "btn admin-activity-toggle"
								aria-label = "Show activity options"
								aria-controls = "admin-activity-results"
								aria-expanded = "false"
							>
								<i class = "bi bi-chevron-down" aria-hidden = "true"></i >
							</button >
						</div >
						<div
							class = "admin-activity-results"
							id = "admin-activity-results"
							aria-label = "Matching activities"
							hidden
						>
							<?php foreach ( $events as $event ):if ( $event['status'] === 'approved' ): ?>
								<button type = "button"
								class = "admin-activity-result"
								data-activity-id = "<?= (int)$event['id'] ?>"
								data-activity-title = "<?= adminEscape($event['title']) ?>"
								data-activity-category = "<?= adminEscape($event['activity_type']) ?>"
								data-activity-date = "<?= adminEscape($event['date']) ?>"
								data-activity-time = "<?= adminEscape($event['time']) ?>"
								data-activity-end-time = "<?= adminEscape($event['end_time']) ?>"
								data-activity-venue = "<?= adminEscape($event['venue']) ?>"
								data-activity-table = "<?= adminEscape($event['table_name']) ?>"
								data-activity-price = "<?= adminEscape($event['price']) ?>"
								data-activity-description = "<?= adminEscape($event['description']) ?>"><?= adminEscape(
								$event['title']
							) ?></button ><?php endif; endforeach; ?>
							<p class = "admin-activity-no-results" hidden>No matching activities.</p >
						</div >
					</div >
					<input type = "hidden" name = "activity_id" value = "<?= (int)($_POST['activity_id'] ?? 0) ?>">
					<div class = "admin-activity-details" hidden aria-live = "polite"></div ></div ><?php endif; ?>
				<?php
				foreach ( $edit ? [ 'first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email', 'contact_number' => 'Contact Number', 'date' => 'Date', 'time' => 'Time' ] : [ 'first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email', 'contact_number' => 'Contact Number' ]
					as $field => $label
				): ?>
					<div class = "col-md-4">
					<label class = "form-label" for = "<?= $field ?>"><?= $label ?></label >
					<input
						class = "form-control"
						id = "<?= $field ?>"
						name = "<?= $field ?>"
						type = "<?= in_array($field, [ 'email', 'date', 'time' ], true) ? $field : 'text' ?>"
						value = "<?= adminEscape($edit[$field] ?? ($_POST[$field] ?? '')) ?>" <?= in_array(
						$field, [ 'email', 'first_name', 'last_name', 'contact_number' ], true
					) ? 'required' : '' ?>></div ><?php endforeach; ?>
				<?php if ( $edit ): ?>
					<div class = "col-md-4">
					<label class = "form-label" for = "status">Status</label >
					<select class = "form-select" id = "status" name = "status"><?php
						foreach ( [ 'pending', 'approved', 'rejected' ] as $status ): ?>
							<option value = "<?= $status ?>" <?= ($edit['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= ucfirst(
							$status
						) ?></option ><?php endforeach; ?></select ></div ><?php endif; ?>
				<div class = "col-12">
					<button class = "btn btn-primary">Save Registration</button > <?php if ( $edit ): ?>
						<a
							class = "btn btn-outline-secondary"
							href = "manage_activities.php?tab=registrations"
						>Cancel
						</a ><?php endif; ?></div >
			</form ><?php adminDisclosureEnd(); ?>

			<section class = "admin-panel">
				<h2 class = "h4">Activity Registrations</h2 >
				<?php foreach ( [
					[ 'Pending', array_values(
						array_filter($rows, fn($row) => $row['status'] === 'pending')
					), 'pending-registrations-pagination', 'No pending activity registrations are available.' ],
					[ 'Approved', array_values(
						array_filter($rows, fn($row) => $row['status'] === 'approved')
					), 'approved-registrations-pagination', 'No approved activity registrations are available.' ],
					[ 'Rejected', array_values(
						array_filter($rows, fn($row) => $row['status'] === 'rejected')
					), 'rejected-registrations-pagination', 'No rejected activity registrations are available.' ]
				] as $index => [$heading, $registrationRows, $paginationId, $emptyMessage]
				): ?>
					<?php if ( $index > 0 ): ?>
						<hr class = "admin-registration-divider"><?php endif; ?>
					<h3 class = "h5"><?= $heading ?></h3 >
					<?php if ( !$registrationRows ): ?>
						<p class = "mb-0 admin-registration-empty"><?= $emptyMessage ?></p ><?php else: ?>
						<div class = "table-responsive">
						<table class = "table table-striped align-middle">
							<thead >
							<tr >
								<th >ID</th >
								<th >Activity</th >
								<th >Email</th >
								<th >Name</th >
								<th >Date / Time</th >
								<th >Contact</th >
								<th >Status</th >
								<th >Actions</th >
							</tr >
							</thead >
							<tbody data-admin-pagination = "<?= $paginationId ?>" data-admin-page-size = "5"><?php
							foreach ( $registrationRows as $row ): ?>
								<tr >
								<td ><?= (int)$row['id'] ?></td >
								<td ><?= adminEscape($row['activity_title'] ?? 'Not linked to an activity') ?></td >
								<td ><?= adminEscape($row['email']) ?></td >
								<td ><?= adminEscape($row['first_name'].' '.$row['last_name']) ?></td >
								<td ><?= adminEscape(
										adminFormatDate($row['date']).' '.adminFormatTime($row['time'])
									) ?></td >
								<td ><?= adminEscape($row['contact_number']) ?></td >
								<td ><?= adminEscape(ucfirst($row['status'])) ?></td >
								<td >
									<div class = "admin-actions">
										<?php if ( $heading === 'Pending' ) adminReviewActions(
											(int)$row['id'], $row['status'], 'registration'
										); else adminTableActions(
											'edit_delete',
											[ 'id' => (int)$row['id'], 'edit_url' => 'manage_activities.php?edit='.(int)$row['id'], 'confirm' => 'Delete this registration?', 'delete_action' => 'delete', 'id_field' => 'id' ]
										); ?>
									</div >
								</td ></tr ><?php endforeach; ?></tbody >
						</table ></div >
					<nav class = "admin-pagination" aria-label = "<?= $heading ?> registrations pagination">
						<ul id = "<?= $paginationId ?>" class = "pagination justify-content-center mb-0"></ul >
						</nav ><?php endif; endforeach; ?>
			</section >
		</div >
	</main >
	<script src = "components/pagination.js?v=<?= filemtime(__DIR__."/components/pagination.js") ?>"></script >
	<script
		src = "components/admin_pagination.js?v=<?= filemtime(
			__DIR__."/components/admin_pagination.js"
		) ?>"
	></script >
	<script
		src = "components/admin_activity_picker.js?v=<?= filemtime(
			__DIR__."/components/admin_activity_picker.js"
		) ?>"
	></script >
	<script src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script >
	</body >
</html >
