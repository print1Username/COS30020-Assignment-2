<?php
session_start();

if (($_SESSION["logged_in"] ?? false) !== true ||
	($_SESSION["type"] ?? null) !== "user" ||
	!isset($_SESSION["email"])) {
	header("Location: login.php");
	exit;
}

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
	$statement = $pdo->prepare("SELECT id, title, date, time, end_time, price, venue, table_name, image FROM activity_table WHERE id = ? AND record_type = 'event' AND status = 'approved'");
	$statement->execute([$activityId]);
	$selectedActivity = $statement->fetch();
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

	$userStatement = $pdo->prepare("SELECT first_name, last_name FROM user_table WHERE email = ?");
	$userStatement->execute([$_SESSION["email"]]);
	$currentUser = $userStatement->fetch();
	if ($currentUser) {
		$firstName = $currentUser["first_name"];
		$lastName = $currentUser["last_name"];
	}

	if ($_SERVER["REQUEST_METHOD"] === "POST" && $selectedActivity) {
		$firstName = trim($_POST["first_name"] ?? "");
		$lastName = trim($_POST["last_name"] ?? "");
		$contactNumber = trim($_POST["contact_number"] ?? "");
		$email = trim($_POST["email"] ?? "");
		if ($firstName === "" || $lastName === "" || $contactNumber === "" || $email === "") {
			$error = "Please complete all required fields.";
		} elseif (!preg_match("/^[a-zA-Z ]+$/", $firstName) ||
			!preg_match("/^[a-zA-Z ]+$/", $lastName) ||
			strlen($firstName) > 50 || strlen($lastName) > 50) {
			$error = "Names must contain only letters and spaces (maximum 50 characters).";
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 50) {
			$error = "Please enter a valid email address.";
		} elseif (!preg_match("/^[0-9+\\-\\s]+$/", $contactNumber) ||
			strlen($contactNumber) > 15) {
			$error = "Please enter a valid contact number (maximum 15 characters).";
		} else {
			try {
				$check = $pdo->prepare("SELECT id FROM activity_table WHERE record_type = 'registration' AND activity_id = ? AND email = ?");
				$check->execute([$activityId, $email]);
				if ($check->fetch()) {
					$error = "You have already registered for this activity.";
				} else {
					$insert = $pdo->prepare("INSERT INTO activity_table (email, first_name, last_name, date, time, contact_number, status, record_type, activity_id) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'registration', ?)");
					$insert->execute([$email, $firstName, $lastName, $activityDate, $activityStartTime, $contactNumber, $activityId]);
					$_SESSION["activity_registration_success"] = "You have successfully registered for ".$activityTitle.".";
					header("Location: activities.php");
					exit;
				}
			} catch (PDOException $e) {
				error_log($e->getMessage());
				$error = "Unable to save your registration. Please try again.";
			}
		}
	}
} else {
	$activities = $pdo->query("SELECT id, title, activity_type AS type, description, date, time AS start_time, end_time, price, venue, table_name AS `table`, image FROM activity_table WHERE record_type = 'event' AND status = 'approved' ORDER BY date, time")->fetchAll();
}
?>
