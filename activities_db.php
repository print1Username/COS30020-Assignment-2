<?php
require_once __DIR__."/auth.php";
requireUser();

require_once __DIR__."/db_setup.php";
require_once __DIR__."/db_connect.php";

if (($activityDbPage ?? "list") === "registration") {
	$error = "";
	$firstName = "";
	$lastName = "";
	$email = $_SESSION["email"];
	$contactNumber = "";
	$activityId = isset($_GET["id"])
		? (int)$_GET["id"]
		: (int)($_POST["activity_id"] ?? 0);
	$statement = $pdo -> prepare("SELECT id, title, date, time, end_time, price, venue, table_name, image FROM activity_info WHERE id = ? AND status = 'approved'");
	$statement -> execute([$activityId]);
	$selectedActivity = $statement -> fetch();
	$activityTitle = $selectedActivity["title"] ?? "";
	$activityDate = $selectedActivity["date"] ?? "";
	$activityStartTime = $selectedActivity["time"] ?? "";
	$activityEndTime = $selectedActivity["end_time"] ?? "";
	$activityPrice = $selectedActivity["price"] ?? 0;
	$activityVenue = $selectedActivity["venue"] ?? "";
	$activityTable = $selectedActivity["table_name"] ?? "";
	$activityImage = $selectedActivity["image"] ?? null;

	if ($selectedActivity === false) {
		$error = "The selected activity could not be found.";
		$selectedActivity = null;
	}

	$userStatement = $pdo -> prepare("SELECT first_name, last_name FROM user_table WHERE email = ?");
	$userStatement -> execute([$_SESSION["email"]]);
	$currentUser = $userStatement -> fetch();
	if ($currentUser) {
		$firstName = $currentUser["first_name"];
		$lastName = $currentUser["last_name"];
	}

	$registrationStatus = null;
	$registrationStatement = $pdo -> prepare("SELECT status, contact_number FROM activity_table WHERE activity_id = ? AND email = ?");
	$registrationStatement -> execute([$activityId, $_SESSION["email"]]);
	$currentRegistration = $registrationStatement -> fetch();
	if ($currentRegistration) {
		$registrationStatus = $currentRegistration["status"];
		$contactNumber = $currentRegistration["contact_number"];
	}

	if ($_SERVER["REQUEST_METHOD"] === "POST" && $selectedActivity) {
		$action = $_POST["action"] ?? "register";
		if ($action === "cancel_registration") {
			if (!in_array($registrationStatus, ["pending", "approved"], true)) {
				$error = "Only pending or approved registrations can be cancelled.";
			} else {
				try {
					$cancelRegistration = $pdo -> prepare("DELETE FROM activity_table WHERE activity_id = ? AND email = ? AND status IN ('pending', 'approved')");
					$cancelRegistration -> execute([$activityId, $_SESSION["email"]]);
					if ($cancelRegistration -> rowCount() > 0) {
						$_SESSION["activity_registration_success"] = "Your registration for ".$activityTitle." has been cancelled.";
						$_SESSION["activity_registration_notice"] = "cancelled";
						header("Location: activities.php");
						exit;
					}
					$error = "The registration could not be cancelled. Please try again.";
				} catch (PDOException $e) {
					error_log($e -> getMessage());
					$error = "Unable to cancel your registration. Please try again.";
				}
			}
		} else {
			$contactNumber = trim($_POST["contact_number"] ?? "");
			if (in_array($registrationStatus, ["pending", "approved"], true)) {
				$error = $registrationStatus === "pending"
					? "Your registration is pending approval."
					: "You have already joined this activity.";
			} elseif ($firstName === "" || $lastName === "" || $contactNumber === "") {
				$error = "Please complete all required fields.";
			} elseif (!preg_match("/^[a-zA-Z ]+$/", $firstName) ||
				!preg_match("/^[a-zA-Z ]+$/", $lastName) ||
				strlen($firstName) > 50 || strlen($lastName) > 50) {
				$error = "Names must contain only letters and spaces (maximum 50 characters).";
			} elseif (!preg_match("/^[0-9+\\-\\s]+$/", $contactNumber) ||
				strlen($contactNumber) > 15) {
				$error = "Please enter a valid contact number (maximum 15 characters).";
			} else {
				try {
					if ($registrationStatus === "rejected") {
						$retryRegistration = $pdo -> prepare("UPDATE activity_table SET contact_number = ?, status = 'pending' WHERE activity_id = ? AND email = ? AND status = 'rejected'");
						$retryRegistration -> execute([$contactNumber, $activityId, $_SESSION["email"]]);
						if ($retryRegistration -> rowCount() === 0) {
							$error = "Your registration status has changed. Please refresh and try again.";
						}
					} else {
						$check = $pdo -> prepare("SELECT id FROM activity_table WHERE activity_id = ? AND email = ?");
						$check -> execute([$activityId, $_SESSION["email"]]);
						if ($check -> fetch()) {
							$error = "You have already registered for this activity.";
						} else {
							$insert = $pdo -> prepare("INSERT INTO activity_table (email, first_name, last_name, date, time, contact_number, status, activity_id) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
							$insert -> execute([$_SESSION["email"], $firstName, $lastName, $activityDate, $activityStartTime, $contactNumber, $activityId]);
						}
					}
					if ($error === "") {
						$_SESSION["activity_registration_success"] = "You have successfully registered for ".$activityTitle.".";
						$_SESSION["activity_registration_notice"] = "pending";
						header("Location: activity_reg.php?id=".urlencode((string)$activityId));
						exit;
					}
				} catch (PDOException $e) {
					error_log($e -> getMessage());
					$error = "Unable to save your registration. Please try again.";
				}
			}
		}
	}
} else {
	$activitiesStatement = $pdo -> prepare("SELECT event.id, event.title, event.activity_type AS type, event.description, event.date, event.time AS start_time, event.end_time, event.price, event.venue, event.table_name AS `table`, event.image, (SELECT registration.status FROM activity_table AS registration WHERE registration.activity_id = event.id AND registration.email = ? ORDER BY registration.id DESC LIMIT 1) AS registration_status FROM activity_info AS event WHERE event.status = 'approved' ORDER BY event.date, event.time");
	$activitiesStatement -> execute([$_SESSION["email"]]);
	$activities = $activitiesStatement -> fetchAll();
}
?>
