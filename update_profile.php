<?php
require_once __DIR__."/update_profile_db.php";
?>

<!DOCTYPE html>
<html lang="en">
	<head>
    <link rel="icon" href="public/logo.ico" sizes="any">
    <link rel="icon" type="image/png" href="public/logo.png">

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Update Profile</title>

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
			<h1>Update Profile</h1>
			<p>Update your personal information and profile picture.</p>
		</div>

		<?php if ($error !== ""): ?>
			<div class="alert alert-danger profile-alert" role="alert">
				<?php echo htmlspecialchars($error); ?>
			</div>
		<?php endif; ?>

		<form
			action="update_profile.php"
			method="POST"
			enctype="multipart/form-data"
		>
			<div class="profile-layout">
				<section class="profile-card">
					<div class="profile-picture-container">
						<?php if ($profileImagePath !== ""): ?>
							<img
								src="<?php echo htmlspecialchars($profileImagePath); ?>"
								alt="Profile picture"
								class="profile-picture"
								id="profileImage"
							>
						<?php else: ?>
							<div
								class="profile-picture profile-picture-default"
								id="profileImage"
								aria-label="Default profile picture"
							>
								<i class="bi bi-person-fill"></i>
							</div>
						<?php endif; ?>
					</div>

					<div class="profile-picture-actions">
						<button
							type="button"
							class="btn profile-upload-button"
							id="uploadPhotoButton"
						>
							<i class="bi bi-upload me-2"></i>
							Upload Profile
						</button>

						<button
							type="button"
							class="btn profile-camera-button"
						>
							<i class="bi bi-camera me-2"></i>
							Take from Camera
						</button>

						<input
							type="file"
							id="profileImageInput"
							name="profile_image"
							accept="image/jpeg,image/png"
							hidden
						>
					</div>
				</section>

				<section class="profile-information">
					<div class="profile-actions">
						<a
							href="profile.php"
							class="btn profile-cancel-button"
						>
							<i class="bi bi-x-lg me-2"></i>
							Cancel
						</a>

						<button
							type="submit"
							class="btn profile-edit-button"
						>
							<i class="bi bi-check-lg me-2"></i>
							Update
						</button>
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
								<input type="email" name="email" class="form-control"
									value="<?php echo htmlspecialchars($userData["Email"]); ?>" required>
							</div>
						</div>
					</div>

					<div class="profile-edit-field-row">
						<div class="profile-edit-field">
							<label for="first_name">First Name</label>
							<input
								type="text"
								id="first_name"
								name="first_name"
								class="form-control"
								value="<?php echo htmlspecialchars($userData["First Name"]); ?>"
								required
							>
						</div>

						<div class="profile-edit-field">
							<label for="last_name">Last Name</label>
							<input
								type="text"
								id="last_name"
								name="last_name"
								class="form-control"
								value="<?php echo htmlspecialchars($userData["LastName"]); ?>"
								required
							>
						</div>
					</div>

					<div class="profile-edit-field">
						<label for="dob">Date of Birth</label>
						<input
							type="date"
							id="dob"
							name="dob"
							class="form-control"
							value="<?php echo htmlspecialchars($dobInputValue); ?>"
							required
						>
					</div>

					<div class="profile-edit-field">
						<label for="gender">Gender</label>
						<select
							id="gender"
							name="gender"
							class="form-select"
							required
						>
							<option
								value="Female"
								<?php echo $userData["Gender"] === "Female" ? "selected" : ""; ?>
							>
								Female
							</option>
							<option
								value="Male"
								<?php echo $userData["Gender"] === "Male" ? "selected" : ""; ?>
							>
								Male
							</option>
						</select>
					</div>

					<div class="profile-edit-field profile-hometown-field">
						<label for="hometown">Hometown</label>
						<textarea
							id="hometown"
							name="hometown"
							class="form-control profile-hometown-input"
							rows="3"
							required
						><?php echo htmlspecialchars($userData["Hometown"]); ?></textarea>
					</div>

					<div class="profile-edit-field">
						<label for="resume_file">Supporting document (PDF, max 7MB)</label>
						<input type="file" id="resume_file" name="resume_file" class="form-control" accept="application/pdf">
					</div>
				</section>
			</div>
		</form>
	</main>

	<div
		class="profile-crop-modal"
		id="profileCropModal"
		aria-hidden="true"
	>
		<div class="profile-crop-wrapper">

			<div class="profile-crop-container">
				<canvas id="profileCropCanvas"></canvas>
			</div>

			<div class="profile-crop-actions">

				<button
					type="button"
					class="profile-crop-cancel"
					id="cropCancelButton"
					aria-label="Cancel crop"
				>
					<i class="bi bi-x-lg"></i>
				</button>

				<button
					type="button"
					class="profile-crop-confirm"
					id="cropConfirmButton"
					aria-label="Confirm crop"
				>
					<i class="bi bi-check-lg"></i>
				</button>

			</div>

		</div>
	</div>

	<script src="components/profile_crop.js"></script>
	<?php if ($_SESSION["type"] === "admin"): ?><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script><?php endif; ?>
	</body>
</html>
