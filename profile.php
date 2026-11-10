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
		<link rel="stylesheet" href="style/profile.css">
	</head>

	<body>
	<div id="navbar-container"></div>

	<?php include "navbar.php"; ?>

	<main class="profile-page container py-5">
		<div class="profile-page-heading">
			<h1>Profile</h1>
			<p>View and manage your personal information.</p>
		</div>

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
						<a
							href="update_profile.php"
							class="btn profile-upload-button"
						>
							<i class="bi bi-upload me-2"></i>
							Upload Profile
						</a>

						<a
							href="update_profile.php"
							class="btn profile-camera-button"
						>
							<i class="bi bi-camera me-2"></i>
							Take from Camera
						</a>
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

				<section class="academic-integrity-section">
					<h2>Academic Integrity Declaration</h2>

					<div class="academic-integrity-box">
						<!-- Official academic integrity declaration goes here. -->
					</div>
				</section>
			</section>
		</div>
	</main>
	</body>
</html>
