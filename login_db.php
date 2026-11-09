<?php
session_start();

$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	$email = trim($_POST["email"] ?? "");
	$password = $_POST["password"] ?? "";

	// Check required fields
	if ($email === "" || $password === "") {

		$error = "Please enter both your email and password.";

	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = "Please enter a valid email address.";

	} else {
		require_once __DIR__."/db_setup.php";
		require_once __DIR__."/db_connect.php";
		$statement = $pdo -> prepare("SELECT email, password, type FROM account_table WHERE email = ?");
		$statement -> execute([$email]);
		$account = $statement -> fetch();

		// Login successful
		if ($account && password_verify($password, $account["password"])) {
			session_regenerate_id(true);
			$_SESSION["logged_in"] = true;
			$_SESSION["email"] = $account["email"];
			$_SESSION["type"] = $account["type"];

			header("Location: ".($account["type"] === "admin" ? "main_menu_admin.php" : "main_menu.php"));
			exit;

		} else {
			$error = "Invalid email or password. Please try again.";

		}
	}
}
?>
