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
$statement = $pdo -> prepare("SELECT email, first_name, last_name, dob, gender, hometown, profile_image, resume_file, resume_filename, OCTET_LENGTH(profile_image_data) AS profile_image_size FROM user_table WHERE email = ?");
$statement -> execute([$currentEmail]);
$user = $statement -> fetch();
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
$profileImage = $user["profile_image_size"] ?? null;

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
	} elseif (!$dobDate || $dobDate -> format("Y-m-d") !== $dob) {
		$error = "Please enter a valid date of birth.";
	} elseif (strlen($hometown) > 50) {
		$error = "Hometown must be 50 characters or fewer.";
	}

	if ($error === "") {
		$newFiles = [];
		try {
			if (strcasecmp($email, $currentEmail) !== 0) {
				$check = $pdo -> prepare("SELECT email FROM user_table WHERE email = ?");
				$check -> execute([$email]);
				if ($check -> fetch()) {
					throw new RuntimeException("This email address is already registered.");
				}
			}
			$newImagePath = null;
			$newImageMime = null;
			$newResume = $user["resume_file"];
			$newResumeFilename = $user["resume_filename"];
			if (isset($_FILES["profile_image"]) &&
				$_FILES["profile_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
				$imageFile = $_FILES["profile_image"];
				if (($imageFile["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
					($imageFile["size"] ?? 0) > 5 * 1024 * 1024 || ($imageFile["size"] ?? 0) < 1 ||
					!is_uploaded_file($imageFile["tmp_name"])) {
					throw new RuntimeException("The image exceeds the allowed size or could not be uploaded.");
				}
				$extension = strtolower(pathinfo($imageFile["name"], PATHINFO_EXTENSION));
				$newImageMime = (new finfo(FILEINFO_MIME_TYPE))->file($imageFile["tmp_name"]);
				$allowedImages = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"];
				if (!isset($allowedImages[$extension]) || $allowedImages[$extension] !== $newImageMime ||
					getimagesize($imageFile["tmp_name"]) === false) {
					throw new RuntimeException("Please upload a valid JPG, JPEG, or PNG image.");
				}
				$newImagePath = $imageFile["tmp_name"];
			}
			if (isset($_FILES["resume_file"]) &&
				$_FILES["resume_file"]["error"] !== UPLOAD_ERR_NO_FILE) {
				$newResumeFilename = basename(str_replace("\\", "/", $_FILES["resume_file"]["name"]));
				if (strlen($newResumeFilename) > 255) {
					throw new RuntimeException("The PDF filename is too long.");
				}
				$newResume = saveValidatedUpload(
					$_FILES["resume_file"], "resume",
					["pdf" => "application/pdf"], 7 * 1024 * 1024
				);
				$newFiles[] = $newResume;
			}
			$pdo -> beginTransaction();
			$update = $pdo -> prepare("UPDATE user_table SET email = ?, first_name = ?, last_name = ?, dob = ?, gender = ?, hometown = ?, resume_file = ?, resume_filename = ? WHERE email = ?");
			$update -> execute([$email, $firstName, $lastName, $dob, $gender, $hometown, $newResume, $newResumeFilename, $currentEmail]);
			if ($newImagePath !== null) {
				storeProfileImage($pdo, $email, $newImageMime, $newImagePath);
			}
			$pdo -> commit();
			$_SESSION["email"] = $email;
			header("Location: profile.php");
			exit;
		} catch (RuntimeException|PDOException $e) {
			if ($pdo -> inTransaction()) {
				$pdo -> rollBack();
			}
			$error = $e instanceof PDOException
				? "The profile could not be updated. Please try again."
				: $e -> getMessage();
		}
	}
}

$fullName = trim($userData["First Name"]." ".$userData["LastName"]);
$dobInputValue = $userData["DOB"];
$profileImagePath = !empty($profileImage) ? "profile_image.php" : "";
?>
