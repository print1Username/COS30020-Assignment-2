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

if (($_SESSION["logged_in"] ?? false) !== true ||
	($_SESSION["type"] ?? null) !== "user" ||
	!isset($_SESSION["email"])) {
	header("Location: login.php");
	exit;
}

$currentEmail = $_SESSION["email"];
require_once __DIR__."/db_setup.php";
require_once __DIR__."/db_connect.php";
$statement = $pdo -> prepare("SELECT email, first_name, last_name, dob, gender, hometown, profile_image, OCTET_LENGTH(profile_image_data) AS profile_image_size, resume_file FROM user_table WHERE email = ?");
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
$profileImage = $user["profile_image"] ?? "";

$fullName = trim(
	$userData["First Name"]." ".$userData["LastName"]
);

$formattedDob = "";

if ($userData["DOB"] !== "") {
	$dobDate = DateTime::createFromFormat(
		"Y-m-d",
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

$profileImagePath = !empty($user["profile_image_size"]) ? "profile_image.php" : "";

if ($profileImagePath === "" && is_string($profileImage) && $profileImage !== "") {
	$possibleImagePath = $profileImage;

	if (str_starts_with($possibleImagePath, "profile_images/") && file_exists(__DIR__."/".$possibleImagePath)) {
		$profileImagePath = $possibleImagePath;
	}
}
?>
