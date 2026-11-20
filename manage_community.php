<?php $reviewKind = "community";
require_once __DIR__."/manage_review_db.php";
require_once __DIR__."/manage_community_info_db.php";
require_once __DIR__."/file_upload.php";
require_once __DIR__."/admin_disclosure.php";
require_once __DIR__."/admin_table_actions.php"; ?>
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
		<title >Manage Community | Boardgame Hub</title >
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
			href = "style/admin_theme.css?v=<?php echo filemtime(__DIR__."/style/admin_theme.css"); ?>"
		>
		<link
			rel = "stylesheet"
			href = "style/admin_pagination.css?v=2"
		>
		<link
			rel = "stylesheet"
			href = "style/file_upload.css?v=<?php echo filemtime(__DIR__."/style/file_upload.css"); ?>"
		>
		<link
			rel = "stylesheet"
			href = "style/manage_community.css?v=<?= filemtime(__DIR__."/style/manage_community.css") ?>"
		>
	</head >
	<body ><?php include __DIR__."/admin_nav.php"; ?>
	<main class = "container py-5"><?php
		$communityTab = $_GET['tab'] ?? 'create';
		if ( !in_array($communityTab, [ 'create', 'join', 'post' ], true) ) $communityTab = 'create';
		if ( $communityEdit || $communityError ) $communityTab = 'create';
		if ( $edit || $error ) $communityTab = 'post';
		if ( $joinError ) $communityTab = 'join';
		?>
		<h1 >Manage Community</h1 >
		<p >
			<a
				class = "btn admin-back-button"
				href = "main_menu_admin.php"
			>Back to Admin Menu
			</a >
		</p >
		<nav
			class = "admin-community-tabs"
			aria-label = "Community management sections"
		>
			<?php
			foreach ( [ 'create' => 'Communities', 'join' => 'Manage Community Members', 'post' => 'Community Posts' ]
				as $tab
			=> $label
			): ?>
				<a
					class = "admin-community-tab<?= $communityTab === $tab ? ' active' : '' ?>"
					href = "manage_community.php?tab=<?= $tab ?>" <?= $communityTab === $tab ? 'aria-current="page"' : '' ?>><?= $label ?></a >
			<?php endforeach; ?>
		</nav >
		<?php if ( $error ): ?>
			<div class = "alert alert-danger"><?= adminEscape($error) ?></div ><?php endif; ?><?php if ( $notice ): ?>
			<div class = "alert alert-success"><?= adminEscape($notice) ?></div ><?php endif; ?>
		<?php if ( $communityError ): ?>
			<div class = "alert alert-danger"><?= adminEscape($communityError) ?></div ><?php endif; ?>
		<?php if ( $joinError ): ?>
			<div class = "alert alert-danger"><?= adminEscape($joinError) ?></div ><?php endif; ?>
		<div <?= $communityTab !== 'post' ? 'hidden' : '' ?>>
			<?php adminDisclosureStart(
				$edit ? 'Edit Post' : 'Create Post', 'Enter author and post content details',
				(bool)($edit || $error)
			); ?>
			<form
				method = "post"
				enctype = "multipart/form-data"
				class = "admin-disclosure-content"
			>
				<input
					type = "hidden"
					name = "token"
					value = "<?= adminEscape(adminToken()) ?>"
				>
				<input
					type = "hidden"
					name = "action"
					value = "<?= $edit ? 'edit' : 'add' ?>"
				>
				<input
					type = "hidden"
					name = "id"
					value = "<?= (int)($edit['id'] ?? 0) ?>"
				>
				<div class = "admin-post-form-grid">
					<section
						class = "admin-post-form-section"
						aria-labelledby = "post-user-title"
					>
						<h3 id = "post-user-title">User Details</h3 >
						<?php foreach ( [ 'first_name' => 'First Name', 'last_name' => 'Last Name' ] as $field =>
							$label
						):
							?>
							<div >
								<label
									class = "form-label"
									for = "<?= $field ?>"
								><?= $label ?></label >
								<input
									class = "form-control"
									id = "<?= $field ?>"
									name = "<?= $field ?>"
									maxlength = "50"
									required
									value = "<?= adminEscape(
										$edit[$field] ?? ($_POST[$field] ?? $adminUser[$field])
									) ?>"
								>
							</div >
						<?php endforeach; ?>
						<div >
							<label
								class = "form-label"
								for = "email"
							>Email
								<span
									class = "text-muted"
								>(optional)</span >
							</label >
							<input
								class = "form-control"
								id = "email"
								name = "email"
								type = "email"
								maxlength = "50"
								value = "<?= adminEscape($edit['email'] ?? ($_POST['email'] ?? $adminUser['email'])) ?>"
							>
						</div >
					</section >
					<section
						class = "admin-post-form-section"
						aria-labelledby = "post-content-title"
					>
						<h3 id = "post-content-title">Post Content</h3 >
						<div
							class = "admin-community-picker"
							data-admin-community-picker
						>
							<label
								class = "form-label"
								for = "community-search"
							>Community
							</label >
							<div class = "admin-activity-search-menu">
								<div class = "input-group admin-activity-search-group">
									<input
										class = "form-control"
										id = "community-search"
										type = "search"
										placeholder = "Search communities by name"
										autocomplete = "off"
										required
										aria-controls = "admin-community-results"
										aria-expanded = "false"
									>
									<button
										type = "button"
										class = "btn admin-activity-toggle"
										aria-label = "Show community options"
										aria-controls = "admin-community-results"
										aria-expanded = "false"
									>
										<i
											class = "bi bi-chevron-down"
											aria-hidden = "true"
										></i >
									</button >
								</div >
								<input
									type = "hidden"
									name = "community_id"
									value = "<?= (int)($edit['community_id'] ?? ($_POST['community_id'] ?? 0)) ?>"
								>
								<div
									class = "admin-activity-results"
									id = "admin-community-results"
									aria-label = "Matching communities"
									hidden
								>
									<?php foreach ( $communities as $community ): ?>
										<button type = "button"
										class = "admin-activity-result admin-community-result"
										data-community-id = "<?= (int)$community['id'] ?>"
										data-community-title = "<?= adminEscape(
											$community['title']
										) ?>"><?= adminEscape($community['title']) ?></button ><?php
									endforeach; ?>
									<p
										class = "admin-activity-no-results text-muted"
										hidden
									>No matching
										communities.
									</p >
								</div >
							</div >
						</div >
						<div >
							<label
								class = "form-label"
								for = "title"
							>Title
							</label >
							<input
								class = "form-control"
								id = "title"
								name = "title"
								maxlength = "100"
								required
								value = "<?= adminEscape($edit['title'] ?? ($_POST['title'] ?? '')) ?>"
							>
						</div >
						<div >
							<label
								class = "form-label"
								for = "description"
							>Description
							</label >
							<textarea
								class = "form-control"
								id = "description"
								name = "description"
								rows = "5"
								required
							><?= adminEscape($edit['description'] ?? ($_POST['description'] ?? '')) ?></textarea >
						</div >
						<div ><?php
							$currentAdminPostImage = $edit && !empty($edit["contribution_image"]) &&
							is_file(__DIR__."/".$edit["contribution_image"])
								? $edit["contribution_image"] : "";
							renderFileUpload(
								[
									"id" => "contribution_image",
									"name" => "contribution_image",
									"label" => "Post Image",
									"accept" => "image/jpeg,image/png",
									"kind" => "image",
									"types" => "JPG, JPEG and PNG images only",
									"max_size" => "maximum 5MB",
									"current_url" => $currentAdminPostImage,
									"current_name" => $currentAdminPostImage !== "" ? basename(
										$currentAdminPostImage
									) : ""
								]
							);
							?></div >
						<?php if ( $edit ): ?>
							<div class = "admin-post-status">
							<label
								class = "form-label"
								for = "status"
							>Status
							</label >
							<select
								class = "form-select"
								id = "status"
								name = "status"
							><?php foreach ( [ 'pending', 'approved', 'rejected' ] as $status ): ?>
									<option
									value = "<?= $status ?>" <?= ($edit['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= ucfirst(
									$status
								) ?></option ><?php
								endforeach; ?></select ></div ><?php endif; ?>
					</section >
				</div >
				<div class = "admin-post-form-actions">
					<button
						class = "btn btn-primary"
					><?= $edit ? 'Save Post' : 'Create Post' ?></button ><?php if ( $edit ): ?>
						<a
							class = "btn btn-outline-secondary"
							href = "manage_community.php?tab=post"
						>Cancel
						</a ><?php endif; ?></div >
			</form >
			<?php adminDisclosureEnd(); ?>
			<section class = "admin-panel admin-community-posts">
				<h2 class = "h4">Community Posts</h2 >
				<div class = "admin-community-post-picker" data-post-community-picker>
					<label class = "form-label" for = "post-community-search">Search Community</label >
					<div class = "admin-activity-search-menu">
						<div class = "input-group admin-activity-search-group">
							<input
								class = "form-control" id = "post-community-search" type = "search"
								placeholder = "Search communities by name"
								autocomplete = "off" aria-controls = "post-community-results" aria-expanded = "false"
							>
							<button
								type = "button"
								class = "btn admin-activity-toggle"
								aria-label = "Show community options"
								aria-controls = "post-community-results"
								aria-expanded = "false"
							>
								<i class = "bi bi-chevron-down" aria-hidden = "true"></i >
							</button >
						</div >
						<div
							class = "admin-activity-results" id = "post-community-results"
							aria-label = "Matching communities" hidden
						>
							<button
								type = "button" class = "admin-activity-result" data-community-id = ""
								data-community-title = "All Communities"
							>All Communities
							</button >
							<?php foreach ( $communities as $community ): ?>
								<button
									type = "button" class = "admin-activity-result"
									data-community-id = "<?= (int)$community['id'] ?>"
									data-community-title = "<?= adminEscape($community['title']) ?>"
								><?= adminEscape($community['title']) ?></button >
							<?php endforeach; ?>
							<p class = "admin-activity-no-results text-muted" hidden>No matching communities.</p >
						</div >
					</div >
				</div >
				<nav
					class = "admin-community-tabs admin-community-post-status-tabs" role = "tablist"
					aria-label = "Post status"
				>
					<?php foreach ( [ 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected' ] as
						$status => $label
					): ?>
						<button
							type = "button" class = "admin-community-tab<?= $status === 'pending' ? ' active' : '' ?>"
							role = "tab" data-post-status = "<?= $status ?>"
							aria-selected = "<?= $status === 'pending' ? 'true' : 'false' ?>"
							aria-controls = "community-post-results"
						><?= $label ?></button >
					<?php endforeach; ?>
				</nav >
				<div id = "community-post-results" role = "tabpanel">
					<div class = "table-responsive admin-community-post-scroll">
						<table class = "table table-striped align-middle admin-post-table">
							<thead >
							<tr >
								<th >ID</th >
								<th >Community</th >
								<th >Author</th >
								<th >Title</th >
								<th >Description</th >
								<th >Image</th >
								<th >Status</th >
								<th >Posted At</th >
								<th >Actions</th >
							</tr >
							</thead >
							<tbody data-post-pagination>
							<?php foreach ( $rows as $row ): ?>
								<tr
									data-community-id = "<?= (int)$row['community_id'] ?>"
									data-post-status = "<?= adminEscape($row['status']) ?>"
								>
									<td ><?= (int)$row['id'] ?></td >
									<td ><?= adminEscape($row['community_title'] ?? 'Unknown community') ?></td >
									<td class = "admin-community-post-author"><?= adminEscape(
											trim($row['first_name'].' '.$row['last_name'])
										) ?><?php if ( $row['email'] ): ?>
											<small
												class = "text-muted"
											><?= adminEscape($row['email']) ?></small ><?php endif; ?></td >
									<td >
										<strong ><?= adminEscape($row['title']) ?></strong >
									</td >
									<td class = "admin-post-description"><span
											class = "admin-community-post-description"
											title = "<?= adminEscape($row['description']) ?>"
										><?= adminEscape($row['description']) ?></span >
									</td >
									<td ><?php if ( !empty($row['has_community_image']) ): ?>
											<img
											class = "admin-post-thumbnail"
											src = "community_image.php?id=<?= (int)$row['id'] ?>"
											alt = "Post image"
											><?php endif; ?></td >
									<td ><?= adminEscape(ucfirst($row['status'])) ?></td >
									<td ><?= adminEscape(adminFormatDateTime($row['created_at'])) ?></td >
									<td >
										<div class = "admin-actions admin-post-actions">
											<?php if ( $row['status'] === 'pending' ) adminReviewActions(
												(int)$row['id'], $row['status'], 'post'
											); ?>
											<?php adminTableActions(
												'edit_delete', [
													'id' => (int)$row['id'],
													'edit_url' => 'manage_community.php?edit='.(int)$row['id'],
													'confirm' => 'Delete this post?',
													'delete_action' => 'delete',
													'id_field' => 'id'
												]
											); ?>
										</div >
									</td >
								</tr >
							<?php endforeach; ?>
							</tbody >
						</table >
					</div >
					<nav class = "admin-pagination" aria-label = "Community posts pagination">
						<ul id = "posts-pagination" class = "pagination justify-content-center mb-0"></ul >
					</nav >
					<p class = "mb-0 admin-registration-empty" data-post-empty hidden></p >
				</div >
			</section >
		</div >
		<div <?= $communityTab !== 'create' ? 'hidden' : '' ?>>
			<?php adminDisclosureStart(
				$communityEdit ? 'Edit Community' : 'Create Community',
				'Enter community details',
				(bool)($communityEdit || ($communityError && ($_POST['action'] ?? '') !== 'community_delete'))
			); ?>
			<form
				method = "post"
				class = "admin-disclosure-content"
			>
				<input
					type = "hidden"
					name = "token"
					value = "<?= adminEscape(adminToken()) ?>"
				>
				<input
					type = "hidden"
					name = "action"
					value = "<?= $communityEdit ? 'community_edit' : 'community_add' ?>"
				>
				<input
					type = "hidden"
					name = "community_id"
					value = "<?= (int)($communityEdit['id'] ?? 0) ?>"
				>
				<div class = "admin-post-form-section">
					<div >
						<label
							class = "form-label"
							for = "community-title"
						>Name
						</label >
						<input
							class = "form-control"
							id = "community-title"
							name = "community_title"
							maxlength = "100"
							required
							value = "<?= adminEscape($communityEdit['title'] ?? ($_POST['community_title'] ?? '')) ?>"
						>
					</div >
					<div >
						<label
							class = "form-label"
							for = "community-description"
						>Description
							<span class = "text-muted">(optional)</span >
						</label >
						<textarea
							class = "form-control"
							id = "community-description"
							name = "community_description"
							rows = "4"
						><?= adminEscape(
								$communityEdit['description'] ?? ($_POST['community_description'] ?? '')
							) ?></textarea >
					</div >
				</div >
				<div class = "admin-post-form-actions">
					<button
						class = "btn btn-primary"
					><?= $communityEdit ? 'Save Community' : 'Create Community' ?></button ><?php if ( $communityEdit ): ?>
						<a
							class = "btn btn-outline-secondary"
							href = "manage_community.php"
						>Cancel
						</a ><?php endif; ?>
				</div >
			</form >
			<?php adminDisclosureEnd(); ?>
			<section class = "admin-panel">
				<h2 class = "h4">Manage Community</h2 >
				<?php if ( !$communityRows ): ?>
					<p class = "mb-0">No communities are available.</p ><?php else: ?>
					<div class = "table-responsive">
						<table class = "table table-striped align-middle">
							<thead >
							<tr >
								<th >ID</th >
								<th >Name</th >
								<th >Description</th >
								<th >Members</th >
								<th >Posts</th >
								<th >Created At</th >
								<th >Actions</th >
							</tr >
							</thead >
							<tbody
								data-admin-pagination = "communities-pagination"
								data-admin-page-size = "5"
							>
							<?php foreach ( $communityRows as $communityRow ): ?>
								<tr >
								<td ><?= (int)$communityRow['id'] ?></td >
								<td >
									<strong ><?= adminEscape($communityRow['title']) ?></strong >
								</td >
								<td class = "admin-post-description"><?= adminEscape(
										$communityRow['description'] ?? ''
									) ?></td >
								<td ><?= (int)$communityRow['member_count'] ?></td >
								<td ><?= (int)$communityRow['post_count'] ?></td >
								<td ><?= adminEscape(adminFormatDate($communityRow['created_at'])) ?></td >
								<td >
									<div class = "admin-actions admin-post-actions">
										<?php adminTableActions(
											'edit_delete', [
												'id' => (int)$communityRow['id'],
												'edit_url' => 'manage_community.php?community_edit='.(int)$communityRow['id'],
												'confirm' => 'Delete this community?',
												'delete_action' => 'community_delete',
												'id_field' => 'community_id'
											]
										); ?>
									</div >
								</td >
								</tr ><?php endforeach; ?>
							</tbody >
						</table >
					</div >
					<nav
						class = "admin-pagination"
						aria-label = "Communities pagination"
					>
						<ul
							id = "communities-pagination"
							class = "pagination justify-content-center mb-0"
						></ul >
					</nav >
				<?php endif; ?>
			</section >
		</div >
		<div <?= $communityTab !== 'join' ? 'hidden' : '' ?>>
			<section class = "admin-panel mb-4">
				<h2 class = "h4">Join Requests</h2 >
				<?php if ( !$joinRows ): ?>
					<p class = "mb-0 admin-registration-empty">No pending join requests are
						available.
					</p ><?php else: ?>
					<div class = "table-responsive">
						<table class = "table table-striped align-middle">
							<thead >
							<tr >
								<th >ID</th >
								<th >Community</th >
								<th >Member</th >
								<th >Status</th >
								<th >Actions</th >
							</tr >
							</thead >
							<tbody
								data-admin-pagination = "join-pagination"
								data-admin-page-size = "5"
							>
							<?php foreach ( $joinRows as $joinRow ): ?>
								<tr >
								<td ><?= (int)$joinRow['id'] ?></td >
								<td ><?= adminEscape($joinRow['community_title']) ?></td >
								<td ><?= adminEscape(trim($joinRow['first_name'].' '.$joinRow['last_name'])) ?>
									<br >
									<small
										class = "text-muted"
									><?= adminEscape($joinRow['email']) ?></small >
								</td >
								<td >Pending</td >
								<td >
									<div class = "admin-actions admin-post-actions">
										<?php adminReviewActions(
											(int)$joinRow['id'], 'pending', 'join request', true
										); ?>
									</div >
								</td >
								</tr ><?php endforeach; ?>
							</tbody >
						</table >
					</div >
					<nav
						class = "admin-pagination"
						aria-label = "Join requests pagination"
					>
						<ul
							id = "join-pagination"
							class = "pagination justify-content-center mb-0"
						></ul >
					</nav >
				<?php endif; ?>
			</section >
			<section class = "admin-panel">
				<h2 class = "h4">Community Members</h2 >
				<?php if ( !$memberRows ): ?>
					<p class = "mb-0 admin-registration-empty">No community members are available.</p >
				<?php else: ?>
					<div class = "table-responsive">
						<table class = "table table-striped align-middle">
							<thead >
							<tr >
								<th >ID</th >
								<th >Community</th >
								<th >Member</th >
								<th >Email</th >
								<th >Actions</th >
							</tr >
							</thead >
							<tbody data-admin-pagination = "members-pagination" data-admin-page-size = "5">
							<?php foreach ( $memberRows as $memberRow ): ?>
								<tr >
								<td ><?= (int)$memberRow['id'] ?></td >
								<td ><?= adminEscape($memberRow['community_title']) ?></td >
								<td ><?= adminEscape(
										trim($memberRow['first_name'].' '.$memberRow['last_name'])
									) ?></td >
								<td ><?= adminEscape($memberRow['email']) ?></td >
								<td >
									<div class = "admin-actions admin-post-actions">
										<?php adminTableActions(
											'delete_only', [
												'id' => (int)$memberRow['id'],
												'confirm' => 'Remove this member from the community?',
												'delete_action' => 'member_remove',
												'id_field' => 'member_id'
											]
										); ?>
									</div >
								</td >
								</tr ><?php endforeach; ?>
							</tbody >
						</table >
					</div >
					<nav class = "admin-pagination" aria-label = "Community members pagination">
						<ul id = "members-pagination" class = "pagination justify-content-center mb-0"></ul >
					</nav >
				<?php endif; ?>
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
		src = "components/admin_community_picker.js?v=<?php echo filemtime(
			__DIR__.'/components/admin_community_picker.js'
		); ?>"
	></script >
	<script
		src = "components/admin_community_posts.js?v=<?= filemtime(
			__DIR__."/components/admin_community_posts.js"
		) ?>"
	></script >
	<script
		src = "components/file_upload.js?v=<?php echo filemtime(
			__DIR__."/components/file_upload.js"
		); ?>"
	></script >
	<script src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script >
	</body >
</html >
