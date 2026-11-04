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


require_once __DIR__."/db_setup.php";
require_once __DIR__."/db_connect.php";

try {
	$check = $pdo->prepare("SELECT email FROM user_table WHERE email = ?");
	$check->execute([$email]);
	if ($check->fetch()) {
		$_SESSION["registration_errors"] = ["This email address is already registered."];
		header("Location: registration.php");
		exit;
	}

	$pdo->beginTransaction();
	$user = $pdo->prepare("INSERT INTO user_table (email, first_name, last_name, dob, gender, hometown) VALUES (?, ?, ?, ?, ?, ?)");
	$user->execute([$email, $firstName, $lastName, $dob, $gender, $hometown]);
	$account = $pdo->prepare("INSERT INTO account_table (email, password, type) VALUES (?, ?, 'user')");
	$account->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
	$pdo->commit();
} catch (PDOException $e) {
	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}
	error_log($e->getMessage());
	$_SESSION["registration_errors"] = [$e->getCode() === "23000"
		? "This email address is already registered."
		: "Unable to save your registration. Please try again."];
	header("Location: registration.php");
	exit;
}


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

header("Location: login.php");
exit;

?>
