<?php require_once __DIR__."/manage_accounts_db.php";
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
		<title >Manage Accounts | Boardgame Hub</title >
		<link
			href = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel = "stylesheet"
		>
		<link
			rel = "stylesheet"
			href = "style/admin_theme.css?v=<?php echo filemtime(__DIR__."/style/admin_theme.css"); ?>"
		>
		<link
			rel = "stylesheet"
			href = "style/admin_pagination.css?v=2"
		>
	</head >
	<body >
	<?php include __DIR__."/admin_nav.php"; ?>
	<main class = "container py-5">
		<h1 >Manage Users' Accounts</h1 >
		<p >
			<a
				class = "btn admin-back-button"
				href = "main_menu_admin.php"
			>Back to Admin Menu
			</a >
		</p >
		<?php if ( $error ): ?>
			<div class = "alert alert-danger"><?= adminEscape($error) ?></div ><?php endif; ?><?php if ( $notice ): ?>
			<div class = "alert alert-success"><?= adminEscape($notice) ?></div ><?php endif; ?>
		<?php adminDisclosureStart(
			$edit ? "Edit Account" : "Add Account", "Enter user details and account credentials",
			(bool)($edit || $error)
		); ?>
		<form
			method = "post"
			class = "row g-3 admin-disclosure-content"
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
				name = "original_email"
				value = "<?= adminEscape($edit['email'] ?? '') ?>"
			>
			<div class = "col-12">
				<div class = "admin-post-form-section">
					<div class = "row g-3">
			<?php
			foreach ( [ 'email' => 'Email', 'first_name' => 'First Name', 'last_name' => 'Last Name', 'dob' => 'Date of Birth', 'hometown' => 'Hometown' ]
				as $field => $label
			): ?>
				<div class = "col-md-4">
				<label
					class = "form-label"
					for = "<?= $field ?>"
				><?= $label ?></label >
				<input
					class = "form-control"
					id = "<?= $field ?>"
					name = "<?= $field ?>"
					type = "<?= $field === 'dob' ? 'date' : ($field === 'email' ? 'email' : 'text') ?>"
					maxlength = "50"
					value = "<?= adminEscape($edit[$field] ?? '') ?>" <?= $field === 'dob' ? '' : 'required' ?>>
				</div ><?php endforeach; ?>
			<div class = "col-md-4">
				<label
					class = "form-label"
					for = "gender"
				>Gender
				</label >
				<select
					class = "form-select"
					id = "gender"
					name = "gender"
					required
				>
					<option value = "">Select</option ><?php foreach ( [ 'Male', 'Female' ] as $option ): ?>
						<option
						value = "<?= $option ?>" <?= ($edit['gender'] ?? '') === $option ? 'selected' : '' ?>><?= $option ?></option ><?php
					endforeach; ?></select >
			</div >
			<div class = "col-md-4">
				<label
					class = "form-label"
					for = "type"
				>Account Type
				</label >
				<select
					class = "form-select"
					id = "type"
					name = "type"
					required
				><?php foreach ( [ 'user', 'admin' ] as $option ): ?>
						<option
						value = "<?= $option ?>" <?= ($edit['type'] ?? 'user') === $option ? 'selected' : '' ?>><?= ucfirst(
						$option
					) ?></option ><?php
					endforeach; ?></select >
			</div >
			<div class = "col-md-4">
				<label
					class = "form-label"
					for = "password"
				>Password <?= $edit ? '(leave blank to keep)' : '' ?></label >
				<input
					class = "form-control"
					id = "password"
					name = "password"
					type = "password" <?= $edit ? '' : 'required' ?>>
			</div >
					</div >
				</div >
			</div >
			<div class = "col-12">
				<button
					class = "btn btn-primary"
					type = "submit"
				>Save Account
				</button > <?php if ( $edit ): ?>
					<a
						class = "btn btn-outline-secondary"
						href = "manage_accounts.php"
					>Cancel
					</a ><?php endif; ?></div >
		</form >
		<?php adminDisclosureEnd(); ?>
		<section class = "admin-panel">
			<h2 class = "h4">Accounts</h2 >
			<div class = "table-responsive">
				<table class = "table table-striped align-middle">
					<thead >
					<tr >
						<th >Email</th >
						<th >Name</th >
						<th >Type</th >
						<th >Actions</th >
					</tr >
					</thead >
					<tbody data-admin-pagination = "accounts-pagination"><?php foreach ( $accounts as $account ): ?>
						<tr >
						<td ><?= adminEscape($account['email']) ?></td >
						<td ><?= adminEscape($account['first_name'].' '.$account['last_name']) ?></td >
						<td ><?= adminEscape($account['type']) ?></td >
						<td >
							<div class = "admin-actions">
								<?php adminTableActions(
									'edit_delete', [
										'id' => $account['email'],
										'edit_url' => 'manage_accounts.php?edit='.urlencode($account['email']),
										'confirm' => 'Delete this account?',
										'delete_action' => 'delete',
										'id_field' => 'email',
										'disable_delete' => $account['email'] === $_SESSION['email']
									]
								); ?>
							</div >
						</td ></tr ><?php endforeach; ?></tbody >
				</table >
			</div >
			<nav
				class = "admin-pagination"
				aria-label = "Accounts pagination"
			>
				<ul
					id = "accounts-pagination"
					class = "pagination justify-content-center mb-0"
				></ul >
			</nav >
		</section >
	</main >
	<script src = "components/pagination.js?v=<?= filemtime(__DIR__."/components/pagination.js") ?>"></script >
	<script
		src = "components/admin_pagination.js?v=<?= filemtime(
			__DIR__."/components/admin_pagination.js"
		) ?>"
	></script >
	<script src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script >
	</body >
</html >
