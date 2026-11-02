<?php
session_start();

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

$error = "";
$success = "";
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

			$profileImage = $tempUser["Profile Image"] ?? "";
			break;
		}
	}
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$firstName = trim($_POST["first_name"] ?? "");
	$lastName = trim($_POST["last_name"] ?? "");
	$dob = trim($_POST["dob"] ?? "");
	$gender = trim($_POST["gender"] ?? "");
	$hometown = trim($_POST["hometown"] ?? "");

	if (
		$firstName === "" ||
		$lastName === "" ||
		$dob === "" ||
		$gender === "" ||
		$hometown === ""
	) {
		$error = "Please complete all required fields.";
	} elseif (
		!preg_match("/^[A-Za-z ]+$/", $firstName) ||
		!preg_match("/^[A-Za-z ]+$/", $lastName)
	) {
		$error = "First name and last name can only contain letters and spaces.";
	} elseif (
		$gender !== "Male" &&
		$gender !== "Female"
	) {
		$error = "Please select a valid gender.";
	} else {
		$dobDate = DateTime::createFromFormat("Y-m-d", $dob);

		if ($dobDate === false) {
			$error = "Please enter a valid date of birth.";
		} else {
			$storedDob = $dobDate -> format("d-m-Y");

			$newProfileImage = $profileImage;

			if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
				if ($_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK) {
					$error = "There was a problem uploading the profile picture.";
				} else {
					$allowedTypes = ["image/jpeg", "image/png", "image/webp"];
					$imageInfo = getimagesize($_FILES["profile_image"]["tmp_name"]);

					if ($imageInfo === false) {
						$error = "Please upload a valid image.";
					} else {
						$fileType = $imageInfo["mime"];

						if (!in_array($fileType, $allowedTypes, true)) {
							$error = "Please upload a JPG, PNG, or WEBP image.";
						} else {
							$profileDirectory = "profile_images";

							if (!is_dir($profileDirectory)) {
								mkdir($profileDirectory, 0777, true);
							}

							$imageFileName = "profile_".sha1(strtolower($currentEmail)).".jpg";
							$imagePath = $profileDirectory."/".$imageFileName;

							if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $imagePath)) {
								$newProfileImage = $imageFileName;
							} else {
								$error = "The profile picture could not be saved.";
							}
						}
					}
				}
			}

			if ($error === "") {
				$updatedRecord =
					"First Name: ".$firstName.
					"|LastName: ".$lastName.
					"|DOB:".$storedDob.
					"|Gender: ".$gender.
					"|Email: ".$userData["Email"].
					"|Hometown:".$hometown.
					"|Password:".$userData["Password"];

				if ($newProfileImage !== "") {
					$updatedRecord .=
						"|Profile Image: ".$newProfileImage;
				}

				$updatedUsers = [];

				foreach ($users as $user) {
					$fields = explode("|", $user);
					$tempEmail = "";

					foreach ($fields as $field) {
						$parts = explode(":", $field, 2);

						if (count($parts) !== 2) {
							continue;
						}

						$key = trim($parts[0]);
						$value = trim($parts[1]);

						if ($key === "Email") {
							$tempEmail = $value;
						}
					}

					if (
						$tempEmail !== "" &&
						strtolower($tempEmail) === strtolower($currentEmail)
					) {
						$updatedUsers[] = $updatedRecord;
					} else {
						$updatedUsers[] = $user;
					}
				}

				if (
					file_put_contents(
						$userFile,
						implode(PHP_EOL, $updatedUsers).PHP_EOL,
						LOCK_EX
					) !== false
				) {
					$_SESSION["email"] = $userData["Email"];

					header("Location: profile.php");
					exit;
				} else {
					$error = "The profile could not be updated. Please try again.";
				}
			}
		}
	}

	$userData["First Name"] = $firstName;
	$userData["LastName"] = $lastName;
	$userData["DOB"] = "";

	if ($dob !== "") {
		$submittedDob = DateTime::createFromFormat("Y-m-d", $dob);

		if ($submittedDob !== false) {
			$userData["DOB"] = $submittedDob -> format("d-m-Y");
		}
	}

	$userData["Gender"] = $gender;
	$userData["Hometown"] = $hometown;
}

$fullName = trim(
	$userData["First Name"]." ".$userData["LastName"]
);

$dobInputValue = "";

if ($userData["DOB"] !== "") {
	$dobDate = DateTime::createFromFormat(
		"d-m-Y",
		$userData["DOB"]
	);

	if ($dobDate !== false) {
		$dobInputValue = $dobDate -> format("Y-m-d");
	}
}

$profileImagePath = "";

if ($profileImage !== "") {
	$possibleImagePath =
		"profile_images/".
		basename($profileImage);

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
		<link rel="stylesheet" href="style/profile.css">
	</head>

	<body>
	<div id="navbar-container"></div>

	<?php include "navbar.php"; ?>

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
							accept="image/jpeg,image/png,image/webp"
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
								<?php echo htmlspecialchars($userData["Email"]); ?>
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
	</body>
</html>