<?php

session_start();

$errors = [];

/*
|--------------------------------------------------------------------------
| Only accept POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	header("Location: registration.php");
	exit;
}


/*
|--------------------------------------------------------------------------
| Get form data
|--------------------------------------------------------------------------
*/

$firstName = trim($_POST["first_name"] ?? "");
$lastName = trim($_POST["last_name"] ?? "");
$dob = trim($_POST["dob"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$email = trim($_POST["email"] ?? "");
$hometown = trim($_POST["hometown"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";


/*
|--------------------------------------------------------------------------
| Keep old values when validation fails
|--------------------------------------------------------------------------
*/

$_SESSION["registration_old"] = [
	"first_name" => $firstName,
	"last_name" => $lastName,
	"dob" => $dob,
	"gender" => $gender,
	"email" => $email,
	"hometown" => $hometown
];


/*
|--------------------------------------------------------------------------
| Required field validation
|--------------------------------------------------------------------------
*/

if ($firstName === "") {
	$errors[] = "First Name is required.";
}

if ($lastName === "") {
	$errors[] = "Last Name is required.";
}

if ($dob === "") {
	$errors[] = "Date of Birth is required.";
}

if ($gender === "") {
	$errors[] = "Gender is required.";
}

if ($email === "") {
	$errors[] = "Email is required.";
}

if ($hometown === "") {
	$errors[] = "Hometown is required.";
}

if ($password === "") {
	$errors[] = "Password is required.";
}

if ($confirmPassword === "") {
	$errors[] = "Confirm Password is required.";
}


/*
|--------------------------------------------------------------------------
| First Name validation
| Only letters and spaces
|--------------------------------------------------------------------------
*/

if ($firstName !== "" && !preg_match("/^[a-zA-Z ]+$/", $firstName)) {
	$errors[] = "First Name can only contain letters and spaces.";
}


/*
|--------------------------------------------------------------------------
| Last Name validation
|--------------------------------------------------------------------------
*/

if ($lastName !== "" && !preg_match("/^[a-zA-Z ]+$/", $lastName)) {
	$errors[] = "Last Name can only contain letters and spaces.";
}


/*
|--------------------------------------------------------------------------
| Email validation
|--------------------------------------------------------------------------
*/

if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$errors[] = "Please enter a valid email address.";
}


/*
|--------------------------------------------------------------------------
| Gender validation
|--------------------------------------------------------------------------
*/

if ($gender !== "" && $gender !== "Male" && $gender !== "Female") {
	$errors[] = "Invalid gender selected.";
}


/*
|--------------------------------------------------------------------------
| Date validation
|--------------------------------------------------------------------------
*/

if ($dob !== "") {

	$date = DateTime::createFromFormat("Y-m-d", $dob);

	$dateErrors = DateTime::getLastErrors();

	if (
		!$date ||
		($dateErrors !== false &&
			($dateErrors["warning_count"] > 0 || $dateErrors["error_count"] > 0))
	) {
		$errors[] = "Please enter a valid Date of Birth.";
	}
}


/*
|--------------------------------------------------------------------------
| Password validation
|--------------------------------------------------------------------------
*/

if ($password !== "") {

	if (strlen($password) < 8) {
		$errors[] = "Password must be at least 8 characters long.";
	}

	if (!preg_match("/[0-9]/", $password)) {
		$errors[] = "Password must contain at least one number.";
	}

	if (!preg_match("/[^a-zA-Z0-9]/", $password)) {
		$errors[] = "Password must contain at least one symbol.";
	}
}


/*
|--------------------------------------------------------------------------
| Confirm Password validation
|--------------------------------------------------------------------------
*/

if (
	$password !== "" &&
	$confirmPassword !== "" &&
	$password !== $confirmPassword
) {
	$errors[] = "Password and Confirm Password do not match.";
}


/*
|--------------------------------------------------------------------------
| Stop if validation failed
|--------------------------------------------------------------------------
*/

if (!empty($errors)) {

	$_SESSION["registration_errors"] = $errors;

	header("Location: registration.php");
	exit;
}


/*
|--------------------------------------------------------------------------
| User data directory
|--------------------------------------------------------------------------
*/

$userDirectory = "data/User";
$userFile = $userDirectory."/user.txt";

if (!is_dir($userDirectory)) {

	if (!mkdir($userDirectory, 0777, true)) {

		$_SESSION["registration_errors"] = [
			"Unable to create the user data directory."
		];

		header("Location: registration.php");
		exit;
	}
}


/*
|--------------------------------------------------------------------------
| Check existing email
|--------------------------------------------------------------------------
*/

if (file_exists($userFile)) {

	$users = file(
		$userFile,
		FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
	);

	foreach ($users as $user) {

		$fields = explode("|", $user);

		foreach ($fields as $field) {

			$parts = explode(":", $field, 2);

			if (count($parts) !== 2) {
				continue;
			}

			$fieldName = trim($parts[0]);
			$fieldValue = trim($parts[1]);

			if (
				$fieldName === "Email" &&
				strcasecmp($fieldValue, $email) === 0
			) {

				$errors[] = "This email address is already registered.";

				break 2;
			}
		}
	}
}


/*
|--------------------------------------------------------------------------
| Stop if email already exists
|--------------------------------------------------------------------------
*/

if (!empty($errors)) {

	$_SESSION["registration_errors"] = $errors;

	header("Location: registration.php");
	exit;
}


/*
|--------------------------------------------------------------------------
| Convert DOB
| HTML date: YYYY-MM-DD
| Assignment format: DD-MM-YYYY
|--------------------------------------------------------------------------
*/

$dateObject = DateTime::createFromFormat("Y-m-d", $dob);
$formattedDob = $dateObject -> format("d-m-Y");


/*
|--------------------------------------------------------------------------
| Create user record
|--------------------------------------------------------------------------
*/

$userRecord =
	"First Name: ".$firstName.
	"|LastName: ".$lastName.
	"|DOB:".$formattedDob.
	"|Gender: ".$gender.
	"|Email: ".$email.
	"|Hometown:".$hometown.
	"|Password:".$password;


/*
|--------------------------------------------------------------------------
| Save user
|--------------------------------------------------------------------------
*/

$fileHandle = fopen($userFile, "a");

if ($fileHandle === false) {

	$_SESSION["registration_errors"] = [
		"Unable to save your registration. Please try again."
	];

	header("Location: registration.php");
	exit;
}

fwrite($fileHandle, $userRecord.PHP_EOL);

fclose($fileHandle);


/*
|--------------------------------------------------------------------------
| Registration successful
|--------------------------------------------------------------------------
*/

unset($_SESSION["registration_old"]);
unset($_SESSION["registration_errors"]);


/*
|--------------------------------------------------------------------------
| Go to Main Menu
|--------------------------------------------------------------------------
*/

header("Location: main_menu.php");
exit;

?>