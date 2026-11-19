<?php
require_once __DIR__."/profile_db.php";
require_once __DIR__."/file_upload.php";
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<link rel="icon" href="img/logo/logo.ico" sizes="any">
		<link rel="icon" type="image/png" href="img/logo/logo.png">

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Profile</title>

		<link
			href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel="stylesheet"
			integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
			crossorigin="anonymous"
		>

		<link
			rel="stylesheet"
			href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		>

		<link rel="stylesheet" href="style/navbar.css">
		<?php if ($_SESSION["type"] === "admin"): ?>
			<link rel="stylesheet"
				  href="style/admin_theme.css?v=<?php echo filemtime(__DIR__."/style/admin_theme.css"); ?>"><?php endif; ?>
		<link rel="stylesheet" href="style/profile.css?v=<?php echo filemtime(__DIR__."/style/profile.css"); ?>">
		<link
			rel="stylesheet" href="style/file_upload.css?v=<?php echo filemtime(__DIR__."/style/file_upload.css"); ?>"
		>
	</head>

	<body<?php if ($_SESSION["type"] === "admin") echo ' class="admin-profile"'; ?>>
	<?php if ($_SESSION["type"] === "admin"): ?>
		<?php include __DIR__."/admin_nav.php"; ?>
	<?php else: ?>
		<?php include "navbar.php"; ?>
	<?php endif; ?>

	<main class="profile-page container py-5">
		<div class="profile-page-heading">
			<h1>Profile</h1>
			<p>View and manage your personal information.</p>
		</div>
		<?php if ($uploadError !== ""): ?>
			<div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($uploadError); ?></div>
		<?php endif; ?>

		<div class="profile-layout">
			<div class="profile-left-column">
				<section class="profile-card">
					<div class="profile-picture-container">
						<?php if ($profileImagePath !== ""): ?>
							<img
								src="<?php echo htmlspecialchars($profileImagePath); ?>"
								alt="Profile picture"
								class="profile-picture"
							>
						<?php else: ?>
							<div
								class="profile-picture profile-picture-default"
								aria-label="Default profile picture"
							>
								<i class="bi bi-person-fill"></i>
							</div>
						<?php endif; ?>
					</div>

					<div class="profile-picture-actions">
						<form action="profile.php" method="POST" enctype="multipart/form-data">
							<input
								type="hidden" name="upload_token"
								value="<?php echo htmlspecialchars($_SESSION["profile_upload_token"], ENT_QUOTES); ?>"
							>
							<input
								type="file" id="profilePhotoInput" name="profile_image" accept="image/jpeg,image/png"
								hidden
							>
							<button type="button" id="profilePhotoButton" class="btn profile-upload-button"><i
									class="bi bi-upload me-2"
								></i>Upload Profile
							</button>
						</form>
						<form action="profile.php" method="POST" enctype="multipart/form-data">
							<input
								type="hidden" name="upload_token"
								value="<?php echo htmlspecialchars($_SESSION["profile_upload_token"], ENT_QUOTES); ?>"
							>
							<input
								type="file" id="profileCameraInput" name="profile_image" accept="image/jpeg,image/png"
								capture="user" hidden
							>
							<button type="button" id="profileCameraButton" class="btn profile-camera-button"><i
									class="bi bi-camera me-2"
								></i>Take from Camera
							</button>
						</form>
					</div>
				</section>

				<div class="profile-logout">
					<a
						href="profile.php?logout=1"
						class="btn profile-logout-button"
					>
						<i class="bi bi-box-arrow-right me-2"></i>
						Logout
					</a>
				</div>
			</div>

			<section class="profile-information">
				<div class="profile-actions">
					<a
						href="update_profile.php"
						class="btn profile-edit-button"
					>
						<i class="bi bi-pencil me-2"></i>
						Edit
					</a>
				</div>

				<div class="profile-fixed-information">
					<div class="profile-fixed-field">
						<label>Student ID</label>
						<div class="profile-fixed-value">
							YOUR_STUDENT_ID
						</div>
					</div>

					<div class="profile-fixed-field">
						<label>Student Email</label>
						<div class="profile-fixed-value">
							<?php echo htmlspecialchars($userData["Email"]); ?>
						</div>
					</div>
				</div>

				<div class="profile-field">
					<label>Username</label>
					<div class="profile-field-value">
						<?php echo htmlspecialchars($fullName); ?>
					</div>
				</div>

				<div class="profile-field">
					<label>Date of Birth</label>
					<div class="profile-field-value">
						<?php echo htmlspecialchars($formattedDob); ?>
					</div>
				</div>

				<div class="profile-field">
					<label>Gender</label>
					<div class="profile-field-value">
						<?php echo htmlspecialchars($userData["Gender"]); ?>
					</div>
				</div>

				<div class="profile-field profile-hometown-field">
					<label>Hometown</label>
					<div class="profile-field-value profile-hometown-value">
						<?php echo nl2br(htmlspecialchars($userData["Hometown"])); ?>
					</div>
				</div>

				<h2 class="profile-pdf-heading">Supporting Document</h2>
				<form action="profile.php" method="POST" enctype="multipart/form-data">
					<input
						type="hidden" name="upload_token"
						value="<?php echo htmlspecialchars($_SESSION["profile_upload_token"], ENT_QUOTES); ?>"
					>
					<?php renderFileUpload([
						"id" => "profilePdfInput",
						"name" => "resume_file",
						"label" => "Supporting Document",
						"accept" => "application/pdf,.pdf",
						"kind" => "file",
						"types" => "PDF files only",
						"max_size" => "maximum 7MB",
						"current_url" => $resumeAvailable ? $resumePath : "",
						"current_name" => $resumeAvailable ? $resumeName : "",
						"auto_submit" => true,
						"allow_remove" => true,
						"remove_name" => "remove_pdf"
					]); ?>
				</form>

				<section class="academic-integrity-section">
					<h2>Academic Integrity Declaration</h2>

					<div class="academic-integrity-box">
						<!-- Official academic integrity declaration goes here. -->
					</div>
				</section>
			</section>
		</div>
	</main>
	<script>
        for (const [buttonId, inputId] of [["profilePhotoButton", "profilePhotoInput"], ["profileCameraButton", "profileCameraInput"]]) {
            const button = document.getElementById(buttonId);
            const input = document.getElementById(inputId);
            button.addEventListener("click", () => input.click());
            input.addEventListener("change", () => {
                if (input.files.length) input.form.requestSubmit();
            });
        }
	</script>
	<script src="components/file_upload.js?v=<?php echo filemtime(__DIR__."/components/file_upload.js"); ?>"></script>
	<?php if ($_SESSION["type"] === "admin"): ?>
		<script
			src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
		></script><?php endif; ?>
	</body>
</html>
