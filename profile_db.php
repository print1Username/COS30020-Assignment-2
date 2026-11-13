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
	!in_array($_SESSION["type"] ?? null, ["user", "admin"], true) ||
	!isset($_SESSION["email"])) {
	header("Location: login.php");
	exit;
}

$currentEmail = $_SESSION["email"];
require_once __DIR__."/db_setup.php";
require_once __DIR__."/db_connect.php";
require_once __DIR__."/upload_helpers.php";
$uploadError = "";
$_SESSION["profile_upload_token"] ??= bin2hex(random_bytes(32));
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["profile_image"])) {
	if (!hash_equals($_SESSION["profile_upload_token"], $_POST["upload_token"] ?? "")) {
		$uploadError = "Invalid upload request. Please try again.";
	} else {
		try {
			$imageFile = $_FILES["profile_image"];
			if (($imageFile["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
				($imageFile["size"] ?? 0) > 5 * 1024 * 1024 || ($imageFile["size"] ?? 0) < 1 ||
				!is_uploaded_file($imageFile["tmp_name"])) {
				throw new RuntimeException("The image exceeds 5MB or could not be uploaded.");
			}
			$extension = strtolower(pathinfo($imageFile["name"], PATHINFO_EXTENSION));
			$mime = (new finfo(FILEINFO_MIME_TYPE))->file($imageFile["tmp_name"]);
			$allowedImages = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"];
			if (!isset($allowedImages[$extension]) || $allowedImages[$extension] !== $mime ||
				getimagesize($imageFile["tmp_name"]) === false) {
				throw new RuntimeException("Please upload a valid JPG, JPEG, or PNG image.");
			}
			$pdo -> beginTransaction();
			storeProfileImage($pdo, $currentEmail, $mime, $imageFile["tmp_name"]);
			$pdo -> commit();
			header("Location: profile.php");
			exit;
		} catch (RuntimeException|PDOException $e) {
			if ($pdo -> inTransaction()) {
				$pdo -> rollBack();
			}
			$uploadError = $e instanceof PDOException ? "The profile photo could not be saved. Please try again." : $e -> getMessage();
		}
	}
} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && (isset($_FILES["resume_file"]) || isset($_POST["remove_pdf"]))) {
	if (!hash_equals($_SESSION["profile_upload_token"] ?? "", $_POST["upload_token"] ?? "")) {
		$uploadError = "Invalid upload request. Please try again.";
	} elseif (isset($_POST["remove_pdf"])) {
		try {
			$lookup = $pdo -> prepare("SELECT resume_file FROM user_table WHERE email = ?");
			$lookup -> execute([$currentEmail]);
			$oldPath = $lookup -> fetchColumn();
			$pdo -> beginTransaction();
			$remove = $pdo -> prepare("UPDATE user_table SET resume_file = NULL, resume_filename = NULL WHERE email = ?");
			$remove -> execute([$currentEmail]);
			if (is_string($oldPath) && preg_match('~^resume/[a-f0-9]{32}\.pdf$~', $oldPath) && is_file(__DIR__."/".$oldPath)) {
				if (!unlink(__DIR__."/".$oldPath)) {
					throw new RuntimeException("The PDF could not be removed. Please try again.");
				}
			}
			$pdo -> commit();
			header("Location: profile.php");
			exit;
		} catch (RuntimeException|PDOException $e) {
			if ($pdo -> inTransaction()) {
				$pdo -> rollBack();
			}
			$uploadError = $e instanceof PDOException ? "The PDF could not be removed. Please try again." : $e -> getMessage();
		}
	} else {
		$newResume = null;
		try {
			$filename = basename(str_replace("\\", "/", $_FILES["resume_file"]["name"]));
			if (strlen($filename) > 255) {
				throw new RuntimeException("The PDF filename is too long.");
			}
			$newResume = saveValidatedUpload($_FILES["resume_file"], "resume", ["pdf" => "application/pdf"], 7 * 1024 * 1024);
			$update = $pdo -> prepare("UPDATE user_table SET resume_file = ?, resume_filename = ? WHERE email = ?");
			$update -> execute([$newResume, $filename, $currentEmail]);
			header("Location: profile.php");
			exit;
		} catch (RuntimeException|PDOException $e) {
			if ($newResume !== null) {
				unlink(__DIR__."/".$newResume);
			}
			$uploadError = $e instanceof PDOException ? "The PDF could not be saved. Please try again." : $e -> getMessage();
		}
	}
}
$statement = $pdo -> prepare("SELECT email, first_name, last_name, dob, gender, hometown, profile_image, OCTET_LENGTH(profile_image_data) AS profile_image_size, resume_file, resume_filename FROM user_table WHERE email = ?");
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
$resumePath = $user["resume_file"] ?? "";
$resumeAvailable = is_string($resumePath) && preg_match('~^resume/[a-f0-9]{32}\.pdf$~', $resumePath) && is_file(__DIR__."/".$resumePath);
$resumeName = $resumeAvailable ? ($user["resume_filename"] ?: basename($resumePath)) : "";

if ($profileImagePath === "" && is_string($profileImage) && $profileImage !== "") {
	$possibleImagePath = $profileImage;

	if (str_starts_with($possibleImagePath, "profile_images/") && file_exists(__DIR__."/".$possibleImagePath)) {
		$profileImagePath = $possibleImagePath;
	}
}
?>
