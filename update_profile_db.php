<?php
require_once __DIR__."/auth.php";
require_once __DIR__."/validation.php";
requireUser(true);

require_once __DIR__."/db_connect.php";
require_once __DIR__."/upload_helpers.php";

$currentEmail = $_SESSION["email"];
$statement = $pdo -> prepare(
	"SELECT email, first_name, last_name, dob, gender, hometown, profile_image, profile_image_data IS NOT NULL AND OCTET_LENGTH(profile_image_data) > 0 AS has_profile_image, profile_image_mime, resume_file, resume_filename FROM user_table WHERE email = ?"
);
$statement -> execute([ $currentEmail ]);
$user = $statement -> fetch();
if ( !$user ) {
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

if ( $_SERVER["REQUEST_METHOD"] === "POST" ) {
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

	if ( $firstName === "" || $lastName === "" || $dob === "" ||
		$gender === "" || $email === "" || $hometown === "" ) {
		$error = "Please complete all required fields.";
	} elseif ( !isValidName($firstName) || !isValidName($lastName) ) {
		$error = "Names must contain only letters and spaces (maximum 50 characters).";
	} elseif ( !isValidEmail($email) ) {
		$error = "Please enter a valid email address (maximum 50 characters).";
	} elseif ( !in_array($gender, [ "Male", "Female" ], true) ) {
		$error = "Please select a valid gender.";
	} elseif ( !isValidDate($dob) ) {
		$error = "Please enter a valid date of birth.";
	} elseif ( strlen($hometown) > 50 ) {
		$error = "Hometown must be 50 characters or fewer.";
	}

	if ( $error === "" ) {
		$newFiles = [];
		$newProfileImage = null;
		try {
			if ( strcasecmp($email, $currentEmail) !== 0 ) {
				$check = $pdo -> prepare("SELECT email FROM user_table WHERE email = ?");
				$check -> execute([ $email ]);
				if ( $check -> fetch() ) {
					throw new RuntimeException("This email address is already registered.");
				}
			}
			$newImagePath = null;
			$newImageMime = null;
			$newResume = $user["resume_file"];
			$newResumeFilename = $user["resume_filename"];
			if ( isset($_FILES["profile_image"]) &&
				$_FILES["profile_image"]["error"] !== UPLOAD_ERR_NO_FILE ) {
				$allowedImages = [ "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png" ];
				$imageFile = $_FILES["profile_image"];
				$newImageMime = validateUpload($imageFile, $allowedImages, 5 * 1024 * 1024)["mime"];
				$newImagePath = $imageFile["tmp_name"];
			}
			if ( isset($_FILES["resume_file"]) &&
				$_FILES["resume_file"]["error"] !== UPLOAD_ERR_NO_FILE ) {
				$newResumeFilename = basename(str_replace("\\", "/", $_FILES["resume_file"]["name"]));
				if ( strlen($newResumeFilename) > 255 ) {
					throw new RuntimeException("The PDF filename is too long.");
				}
				$newResume = saveValidatedUpload(
					$_FILES["resume_file"], "resume",
					[ "pdf" => "application/pdf" ], 7 * 1024 * 1024
				);
				$newFiles[] = $newResume;
			}
			$pdo -> beginTransaction();
			if ( $newImagePath !== null ) {
				$newProfileImage = storeProfileImage($pdo, $email, $newImageMime, $newImagePath, $currentEmail);
			} elseif ( strcasecmp($email, $currentEmail) !== 0 &&
				is_string($user["profile_image"]) &&
				preg_match('~^profile_images/[^/]+/[a-f0-9]{32}\.(?:jpg|png)$~', $user["profile_image"]) &&
				is_file(__DIR__."/".$user["profile_image"]) ) {
				$existingMime = (new finfo(FILEINFO_MIME_TYPE)) -> file(__DIR__."/".$user["profile_image"]);
				$newProfileImage = storeProfileImage(
					$pdo, $email, $existingMime, __DIR__."/".$user["profile_image"],
					$currentEmail
				);
			}
			$update = $pdo -> prepare(
				"UPDATE user_table SET email = ?, first_name = ?, last_name = ?, dob = ?, gender = ?, hometown = ?, resume_file = ?, resume_filename = ? WHERE email = ?"
			);
			$update -> execute(
				[ $email, $firstName, $lastName, $dob, $gender, $hometown, $newResume, $newResumeFilename, $currentEmail ]
			);
			if ( $newProfileImage !== null ) {
				$updateImage = $pdo -> prepare("UPDATE user_table SET profile_image = ? WHERE email = ?");
				$updateImage -> execute([ $newProfileImage, $email ]);
			}
			$pdo -> commit();
			$_SESSION["email"] = $email;
			header("Location: profile.php");
			exit;

		} catch ( RuntimeException|PDOException $e ) {
			if ( $newProfileImage !== null && is_file(__DIR__."/".$newProfileImage) ) {
				unlink(__DIR__."/".$newProfileImage);
			}
			if ( $pdo -> inTransaction() ) {
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
$profileImagePath = !empty($user["has_profile_image"]) &&
in_array($user["profile_image_mime"], [ "image/jpeg", "image/png" ], true)
	? "profile_image.php"
	: "";
?>
