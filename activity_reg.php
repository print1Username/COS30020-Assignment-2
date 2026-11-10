<?php
$activityDbPage = "registration";
require_once __DIR__."/activities_db.php";
?>

<!DOCTYPE html>
<html lang="en">
	<head>
    <link rel="icon" href="public/logo.ico" sizes="any">
    <link rel="icon" type="image/png" href="public/logo.png">

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
