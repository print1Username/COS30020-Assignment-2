<?php
require_once __DIR__."/profile_db.php";
?>

<!DOCTYPE html>
<html lang="en">
	<head>
    <link rel="icon" href="public/logo.ico" sizes="any">
    <link rel="icon" type="image/png" href="public/logo.png">

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
		<?php if ($_SESSION["type"] === "admin"): ?><link rel="stylesheet" href="style/admin_theme.css?v=<?php echo filemtime(__DIR__."/style/admin_theme.css"); ?>"><?php endif; ?>
		<link rel="stylesheet" href="style/profile.css?v=<?php echo filemtime(__DIR__."/style/profile.css"); ?>">
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
							<input type="hidden" name="upload_token" value="<?php echo htmlspecialchars($_SESSION["profile_upload_token"], ENT_QUOTES); ?>">
							<input type="file" id="profilePhotoInput" name="profile_image" accept="image/jpeg,image/png" hidden>
							<button type="button" id="profilePhotoButton" class="btn profile-upload-button"><i class="bi bi-upload me-2"></i>Upload Profile</button>
						</form>
						<form action="profile.php" method="POST" enctype="multipart/form-data">
							<input type="hidden" name="upload_token" value="<?php echo htmlspecialchars($_SESSION["profile_upload_token"], ENT_QUOTES); ?>">
							<input type="file" id="profileCameraInput" name="profile_image" accept="image/jpeg,image/png" capture="user" hidden>
							<button type="button" id="profileCameraButton" class="btn profile-camera-button"><i class="bi bi-camera me-2"></i>Take from Camera</button>
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
				<form class="profile-pdf-box" action="profile.php" method="POST" enctype="multipart/form-data">
					<div class="profile-pdf-info">
						<i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
						<div class="profile-pdf-text">
							<?php if ($resumeAvailable): ?>
								<a href="<?php echo htmlspecialchars($resumePath, ENT_QUOTES); ?>" target="_blank" rel="noopener" class="profile-pdf-name"><?php echo htmlspecialchars($resumeName); ?></a>
							<?php else: ?>
								<span class="profile-pdf-name">Upload PDF</span>
							<?php endif; ?>
							<small class="profile-pdf-requirements">PDF only, maximum 7MB</small>
						</div>
					</div>
					<input type="hidden" name="upload_token" value="<?php echo htmlspecialchars($_SESSION["profile_upload_token"], ENT_QUOTES); ?>">
					<input type="file" id="profilePdfInput" name="resume_file" accept="application/pdf,.pdf" hidden>
					<div class="profile-pdf-actions">
						<button type="button" id="profilePdfButton" class="btn profile-pdf-button"><i class="bi bi-upload" aria-hidden="true"></i> Upload</button>
						<?php if ($resumeAvailable): ?>
							<button type="submit" name="remove_pdf" value="1" class="btn profile-pdf-remove-button"><i class="bi bi-trash" aria-hidden="true"></i> Remove</button>
						<?php endif; ?>
					</div>
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
		const uploadButton = document.getElementById("profilePdfButton");
		const pdfInput = document.getElementById("profilePdfInput");
		uploadButton.addEventListener("click", () => pdfInput.click());
		pdfInput.addEventListener("change", () => {
			if (pdfInput.files.length) pdfInput.form.requestSubmit();
		});
	</script>
	<?php if ($_SESSION["type"] === "admin"): ?><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script><?php endif; ?>
	</body>
</html>
