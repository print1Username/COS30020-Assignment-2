<?php
session_start();

/*
 * Handle logout
 */
if (isset($_GET["logout"])) {
	session_unset();
	session_destroy();

	header("Location: login.php");
	exit;
}

if (
	!isset($_SESSION["logged_in"]) ||
	$_SESSION["logged_in"] !== true ||
	!isset($_SESSION["email"])
) {
	header("Location: login.php");
	exit;
}

$currentEmail = $_SESSION["email"];
$userFile = "data/User/user.txt";

$userData = [
	"First Name" => "",
	"LastName" => "",
	"DOB" => "",
	"Gender" => "",
	"Email" => "",
	"Hometown" => "",
	"Password" => "",
	"Profile Image" => ""
];

$profileImage = "";

if (file_exists($userFile)) {
	$users = file(
		$userFile,
		FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
	);

	foreach ($users as $user) {
		$fields = explode("|", $user);
		$tempUser = [];

		foreach ($fields as $field) {
			$parts = explode(":", $field, 2);

			if (count($parts) !== 2) {
				continue;
			}

			$key = trim($parts[0]);
			$value = trim($parts[1]);
			$tempUser[$key] = $value;
		}

		if (
			isset($tempUser["Email"]) &&
			strtolower($tempUser["Email"]) === strtolower($currentEmail)
		) {
			foreach ($userData as $key => $value) {
				if (isset($tempUser[$key])) {
					$userData[$key] = $tempUser[$key];
				}
			}

			if (isset($tempUser["Profile Image"])) {
				$profileImage = $tempUser["Profile Image"];
			}

			break;
		}
	}
}

$fullName = trim(
	$userData["First Name"]." ".$userData["LastName"]
);

$formattedDob = "";

if ($userData["DOB"] !== "") {
	$dobDate = DateTime::createFromFormat(
		"d-m-Y",
		$userData["DOB"]
	);

	if ($dobDate !== false) {
		$formattedDob = $dobDate -> format("d F Y");
	} else {
		$timestamp = strtotime($userData["DOB"]);

		if ($timestamp !== false) {
			$formattedDob = date("d F Y", $timestamp);
		}
	}
}

$profileImagePath = "";

if ($profileImage !== "") {
	$possibleImagePath = "profile_images/".basename($profileImage);

	if (file_exists($possibleImagePath)) {
		$profileImagePath = $possibleImagePath;
	}
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
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