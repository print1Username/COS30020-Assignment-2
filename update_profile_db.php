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
require_once __DIR__."/upload_helpers.php";

$currentEmail = $_SESSION["email"];
$statement = $pdo->prepare("SELECT * FROM user_table WHERE email = ?");
$statement->execute([$currentEmail]);
$user = $statement->fetch();
if (!$user) {
	header("Location: login.php");
	exit;
}

$userData = [
	"First Name" => $user["first_name"],
	"LastName" => $user["last_name"],
	"DOB" => $user["dob"] ?? "",
	"Gender" => $user["gender"],
	"Email" => $user["email"],
	"Hometown" => $user["hometown"]
];
$error = "";
$profileImage = $user["profile_image"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$firstName = trim($_POST["first_name"] ?? "");
	$lastName = trim($_POST["last_name"] ?? "");
	$dob = trim($_POST["dob"] ?? "");
	$gender = trim($_POST["gender"] ?? "");
	$email = trim($_POST["email"] ?? "");
	$hometown = trim($_POST["hometown"] ?? "");
	$userData = [
		"First Name" => $firstName,
		"LastName" => $lastName,
		"DOB" => $dob,
		"Gender" => $gender,
		"Email" => $email,
		"Hometown" => $hometown
	];

	$dobDate = DateTime::createFromFormat("!Y-m-d", $dob);
	if ($firstName === "" || $lastName === "" || $dob === "" ||
		$gender === "" || $email === "" || $hometown === "") {
		$error = "Please complete all required fields.";
	} elseif (!preg_match("/^[A-Za-z ]+$/", $firstName) ||
		!preg_match("/^[A-Za-z ]+$/", $lastName) ||
		strlen($firstName) > 50 || strlen($lastName) > 50) {
		$error = "Names must contain only letters and spaces (maximum 50 characters).";
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 50) {
		$error = "Please enter a valid email address (maximum 50 characters).";
	} elseif (!in_array($gender, ["Male", "Female"], true)) {
		$error = "Please select a valid gender.";
	} elseif (!$dobDate || $dobDate->format("Y-m-d") !== $dob) {
		$error = "Please enter a valid date of birth.";
	} elseif (strlen($hometown) > 50) {
		$error = "Hometown must be 50 characters or fewer.";
	}

	if ($error === "") {
		$newFiles = [];
		try {
			if (strcasecmp($email, $currentEmail) !== 0) {
				$check = $pdo->prepare("SELECT email FROM user_table WHERE email = ?");
				$check->execute([$email]);
				if ($check->fetch()) {
					throw new RuntimeException("This email address is already registered.");
				}
			}
			$newImage = $user["profile_image"];
			$newResume = $user["resume_file"];
			if (isset($_FILES["profile_image"]) &&
				$_FILES["profile_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
				$newImage = saveValidatedUpload(
					$_FILES["profile_image"], "profile_images",
					["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"],
					5 * 1024 * 1024
				);
				$newFiles[] = $newImage;
			}
			if (isset($_FILES["resume_file"]) &&
				$_FILES["resume_file"]["error"] !== UPLOAD_ERR_NO_FILE) {
				$newResume = saveValidatedUpload(
					$_FILES["resume_file"], "resume",
					["pdf" => "application/pdf"], 7 * 1024 * 1024
				);
				$newFiles[] = $newResume;
			}
			$pdo->beginTransaction();
			$update = $pdo->prepare("UPDATE user_table SET email = ?, first_name = ?, last_name = ?, dob = ?, gender = ?, hometown = ?, profile_image = ?, resume_file = ? WHERE email = ?");
			$update->execute([$email, $firstName, $lastName, $dob, $gender, $hometown, $newImage, $newResume, $currentEmail]);
			$pdo->commit();
			$_SESSION["email"] = $email;
			header("Location: profile.php");
			exit;
		} catch (RuntimeException | PDOException $e) {
			if ($pdo->inTransaction()) {
				$pdo->rollBack();
			}
			foreach ($newFiles as $path) {
				unlink(__DIR__."/".$path);
			}
			$error = $e instanceof PDOException
				? "The profile could not be updated. Please try again."
				: $e->getMessage();
		}
	}
}

$fullName = trim($userData["First Name"]." ".$userData["LastName"]);
$dobInputValue = $userData["DOB"];
$profileImagePath = "";
if (is_string($profileImage) && $profileImage !== "" &&
	str_starts_with($profileImage, "profile_images/") &&
	file_exists(__DIR__."/".$profileImage)) {
	$profileImagePath = $profileImage;
}
?>
