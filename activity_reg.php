<?php
session_start();

/*
 * ==========================
 * Login Check
 * ==========================
 */

if (
	!isset($_SESSION["logged_in"]) ||
	$_SESSION["logged_in"] !== true ||
	!isset($_SESSION["email"])
) {
	header("Location: login.php");
	exit;
}

/*
 * ==========================
 * File Paths
 * ==========================
 */

$activitiesFile = "data/activities.json";
$userFile = "data/User/user.txt";
$registrationFile = "data/activity_registrations.txt";

/*
 * ==========================
 * Variables
 * ==========================
 */

$error = "";

$firstName = "";
$lastName = "";
$email = "";
$contactNumber = "";

$activityId = 0;
$activityTitle = "";
$activityDate = "";
$activityStartTime = "";
$activityEndTime = "";
$activityPrice = 0;
$activityVenue = "";
$activityTable = "";
$activityImage = null;

/*
 * ==========================
 * Get Activity ID
 * ==========================
 */

$activityId = isset($_GET["id"])
	? (int)$_GET["id"]
	: (int)($_POST["activity_id"] ?? 0);

/*
 * ==========================
 * Load Activities
 * ==========================
 */

$activities = [];

if (file_exists($activitiesFile)) {
	$jsonData = file_get_contents($activitiesFile);
	$activities = json_decode($jsonData, true);

	if (!is_array($activities)) {
		$activities = [];
	}
}

/*
 * ==========================
 * Find Selected Activity
 * ==========================
 */

$selectedActivity = null;

foreach ($activities as $activity) {
	if (
		isset($activity["id"]) &&
		(int)$activity["id"] === $activityId
	) {
		$selectedActivity = $activity;
		break;
	}
}

/*
 * ==========================
 * Check Activity
 * ==========================
 */

if ($selectedActivity === null) {
	$error = "The selected activity could not be found.";
} else {
	$activityTitle = $selectedActivity["title"] ?? "";
	$activityDate = $selectedActivity["date"] ?? "";
	$activityStartTime = $selectedActivity["start_time"] ?? "";
	$activityEndTime = $selectedActivity["end_time"] ?? "";
	$activityPrice = $selectedActivity["price"] ?? 0;
	$activityVenue = $selectedActivity["venue"] ?? "";
	$activityTable = $selectedActivity["table"] ?? "";
	$activityImage = $selectedActivity["image"] ?? null;
}

/*
 * ==========================
 * Load Current User
 * ==========================
 */

$currentEmail = $_SESSION["email"];

if (file_exists($userFile)) {
	$users = file(
		$userFile,
		FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
	);

	foreach ($users as $user) {
		$fields = explode("|", $user);

		$storedFirstName = "";
		$storedLastName = "";
		$storedEmail = "";

		foreach ($fields as $field) {
			$parts = explode(":", $field, 2);

			if (count($parts) !== 2) {
				continue;
			}

			$key = trim($parts[0]);
			$value = trim($parts[1]);

			if ($key === "First Name") {
				$storedFirstName = $value;
			}

			if ($key === "LastName") {
				$storedLastName = $value;
			}

			if ($key === "Email") {
				$storedEmail = $value;
			}
		}

		if (
			strtolower($storedEmail) ===
			strtolower($currentEmail)
		) {
			$firstName = $storedFirstName;
			$lastName = $storedLastName;
			$email = $storedEmail;
			break;
		}
	}
}

/*
 * ==========================
 * Handle Form Submission
 * ==========================
 */

if ($_SERVER["REQUEST_METHOD"] === "POST" && $selectedActivity !== null) {
	$firstName = trim($_POST["first_name"] ?? "");
	$lastName = trim($_POST["last_name"] ?? "");
	$contactNumber = trim($_POST["contact_number"] ?? "");
	$email = trim($_POST["email"] ?? "");

	/*
	 * Required Fields
	 */

	if (
		$firstName === "" ||
		$lastName === "" ||
		$contactNumber === "" ||
		$email === ""
	) {
		$error = "Please complete all required fields.";
	} /*
     * Name Validation
     */

	elseif (
		!preg_match("/^[a-zA-Z ]+$/", $firstName) ||
		!preg_match("/^[a-zA-Z ]+$/", $lastName)
	) {
		$error = "First name and last name may only contain letters and spaces.";
	} /*
     * Email Validation
     */

	elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = "Please enter a valid email address.";
	} /*
     * Contact Number Validation
     */

	elseif (!preg_match("/^[0-9+\-\s]+$/", $contactNumber)) {
		$error = "Please enter a valid contact number.";
	} /*
     * Duplicate Registration Check
     */

	else {
		$duplicateRegistration = false;

		if (file_exists($registrationFile)) {
			$registrations = file(
				$registrationFile,
				FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
			);

			foreach ($registrations as $registration) {
				$fields = explode("|", $registration);

				$registeredEmail = "";
				$registeredActivityId = "";

				foreach ($fields as $field) {
					$parts = explode(":", $field, 2);

					if (count($parts) !== 2) {
						continue;
					}

					$key = trim($parts[0]);
					$value = trim($parts[1]);

					if ($key === "Email") {
						$registeredEmail = $value;
					}

					if ($key === "Activity ID") {
						$registeredActivityId = $value;
					}
				}

				if (
					strtolower($registeredEmail) === strtolower($email) &&
					(int)$registeredActivityId === $activityId
				) {
					$duplicateRegistration = true;
					break;
				}
			}
		}

		if ($duplicateRegistration) {
			$error = "You have already registered for this activity.";
		} else {
			/*
			 * Clean Values Before Saving
			 */

			$firstName = str_replace(
				["|", "\r", "\n"],
				"",
				$firstName
			);

			$lastName = str_replace(
				["|", "\r", "\n"],
				"",
				$lastName
			);

			$contactNumber = str_replace(
				["|", "\r", "\n"],
				"",
				$contactNumber
			);

			$email = str_replace(
				["|", "\r", "\n"],
				"",
				$email
			);

			/*
			 * Registration Record
			 */

			$registrationRecord =
				"Activity ID: ".$activityId.
				"|First Name: ".$firstName.
				"|Last Name: ".$lastName.
				"|Contact Number: ".$contactNumber.
				"|Email: ".$email.
				"|Activity Date: ".$activityDate.
				"|Activity Start Time: ".$activityStartTime.
				"|Activity End Time: ".$activityEndTime.
				"|Activity Title: ".$activityTitle.
				PHP_EOL;

			/*
			 * Save Registration
			 */

			$saveResult = file_put_contents(
				$registrationFile,
				$registrationRecord,
				FILE_APPEND | LOCK_EX
			);

			if ($saveResult === false) {
				$error = "Unable to save your registration. Please try again.";
			} else {
				$_SESSION["activity_registration_success"] =
					"You have successfully registered for ".
					$activityTitle.".";

				header("Location: activities.php");
				exit;
			}
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta
			name="viewport"
			content="width=device-width, initial-scale=1"
		>

		<title>Register for Activity | Boardgame Hub</title>

		<!-- Bootstrap 5.3.8 -->
		<link
			href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
			rel="stylesheet"
			integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
			crossorigin="anonymous"
		>

		<!-- Bootstrap Icons -->
		<link
			rel="stylesheet"
			href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
		>

		<!-- Navbar CSS -->
		<link
			rel="stylesheet"
			href="style/navbar.css"
		>

		<!-- Activity Registration CSS -->
		<link
			rel="stylesheet"
			href="style/activity_reg.css"
		>
	</head>

	<body>
	<?php include "navbar.php"; ?>

	<main class="activity-registration-page">
		<div class="container py-5">

			<!-- Back Button -->
			<div class="activity-registration-back">
				<a
					href="activities.php"
					class="activity-back-button"
				>
					<i class="bi bi-arrow-left"></i>
					<span>Back to Activities</span>
				</a>
			</div>

			<!-- Page Heading -->
			<div class="activity-registration-heading">
                    <span class="activity-registration-eyebrow">
                        BOARDGAME HUB
                    </span>

				<h1>
					Register for Activity
				</h1>

				<p>
					Complete the form below to reserve your place in the selected activity.
				</p>
			</div>

			<!-- Error Message -->
			<?php if ($error !== ""): ?>
				<div
					class="alert alert-danger activity-registration-alert"
					role="alert"
				>
					<i class="bi bi-exclamation-circle me-2"></i>
					<?php echo htmlspecialchars($error); ?>
				</div>
			<?php endif; ?>

			<?php if ($selectedActivity !== null): ?>

				<form
					action="activity_reg.php?id=<?php echo $activityId; ?>"
					method="POST"
					class="activity-registration-form"
				>
					<input
						type="hidden"
						name="activity_id"
						value="<?php echo $activityId; ?>"
					>

					<!-- Selected Activity -->
					<section class="activity-registration-card activity-selected-card">

						<div class="activity-registration-card-header">
							<div>
                                <span class="activity-section-eyebrow">
                                    SELECTED ACTIVITY
                                </span>

								<h2>
									Activity Details
								</h2>
							</div>

							<i class="bi bi-calendar-event"></i>
						</div>

						<!-- Activity Title -->
						<div class="activity-detail-block activity-detail-title">
                            <span class="activity-detail-label">
                                Activity Title
                            </span>

							<h3>
								<?php echo htmlspecialchars($activityTitle); ?>
							</h3>
						</div>

						<!-- Date / Time -->
						<div class="activity-detail-row">
							<div class="activity-detail-block">
                                <span class="activity-detail-label">
                                    Activity Date
                                </span>

								<span class="activity-detail-value">
                                    <?php
									echo htmlspecialchars(
										date(
											"d/m/Y",
											strtotime($activityDate)
										)
									);
									?>
                                </span>
							</div>

							<div class="activity-detail-block">
                                <span class="activity-detail-label">
                                    Activity Time
                                </span>

								<span class="activity-detail-value">
                                    <?php
									echo htmlspecialchars(
										date(
											"g:i A",
											strtotime($activityStartTime)
										).
										" - ".
										date(
											"g:i A",
											strtotime($activityEndTime)
										)
									);
									?>
                                </span>
							</div>
						</div>

						<!-- Venue / Table -->
						<div class="activity-detail-row">
							<div class="activity-detail-block">
                                <span class="activity-detail-label">
                                    Venue
                                </span>

								<span class="activity-detail-value">
                                    <?php echo htmlspecialchars($activityVenue); ?>
                                </span>
							</div>

							<div class="activity-detail-block">
                                <span class="activity-detail-label">
                                    Table
                                </span>

								<span class="activity-detail-value">
                                    <?php echo htmlspecialchars($activityTable); ?>
                                </span>
							</div>
						</div>

						<!-- Registration Fee -->
						<div class="activity-detail-block activity-fee-block">
                            <span class="activity-detail-label">
                                Registration Fee
                            </span>

							<span class="activity-fee-value">
                                RM <?php echo number_format((float)$activityPrice, 2); ?>
                            </span>
						</div>

					</section>

					<!-- User Details -->
					<section class="activity-registration-card">
						<div class="activity-registration-card-header">
							<div>
                                    <span class="activity-section-eyebrow">
                                        USER DETAILS
                                    </span>

								<h2>
									Your Information
								</h2>
							</div>

							<i class="bi bi-person-circle"></i>
						</div>

						<!-- First Name / Last Name -->
						<div class="activity-form-row">
							<div class="activity-form-field">
								<label
									for="first_name"
									class="form-label"
								>
									First Name
								</label>

								<input
									type="text"
									id="first_name"
									name="first_name"
									class="form-control"
									value="<?php echo htmlspecialchars($firstName); ?>"
									required
								>
							</div>

							<div class="activity-form-field">
								<label
									for="last_name"
									class="form-label"
								>
									Last Name
								</label>

								<input
									type="text"
									id="last_name"
									name="last_name"
									class="form-control"
									value="<?php echo htmlspecialchars($lastName); ?>"
									required
								>
							</div>
						</div>

						<!-- Email / Contact Number -->
						<div class="activity-form-row">
							<div class="activity-form-field">
								<label
									for="email"
									class="form-label"
								>
									Email
								</label>

								<input
									type="text"
									id="email"
									name="email"
									class="form-control"
									value="<?php echo htmlspecialchars($email); ?>"
									required
								>
							</div>

							<div class="activity-form-field">
								<label
									for="contact_number"
									class="form-label"
								>
									Contact Number
								</label>

								<input
									type="text"
									id="contact_number"
									name="contact_number"
									class="form-control"
									value="<?php echo htmlspecialchars($contactNumber); ?>"
									placeholder="Enter your contact number"
									required
								>
							</div>
						</div>
					</section>

					<!-- Form Actions -->
					<div class="activity-registration-actions">
						<a
							href="activities.php"
							class="btn activity-cancel-button"
						>
							Cancel
						</a>

						<button
							type="submit"
							class="btn activity-submit-button"
						>
							<i class="bi bi-calendar-check me-2"></i>
							Register for Activity
						</button>
					</div>
				</form>

			<?php endif; ?>
		</div>
	</main>

	<!-- Bootstrap JavaScript -->
	<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYJrWVcXK/BmnVDxM+D2scQbITxI"
		crossorigin="anonymous"
	></script>

	<!-- Activity Registration JavaScript -->
	<script src="components/activity_reg.js"></script>
	</body>
</html>