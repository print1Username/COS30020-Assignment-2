<?php
require_once __DIR__."/admin_db.php";
$error = "";
$edit = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
	adminCheckPost();
	$action = $_POST["action"] ?? "";
	$email = trim($_POST["email"] ?? "");
	try {
		if ($action === "delete") {
			if ($email === $_SESSION["email"]) throw new RuntimeException("You cannot delete your own admin account.");
			$statement = $pdo->prepare("DELETE FROM user_table WHERE email = ?");
			$statement->execute([$email]);
			adminRedirect("manage_accounts.php", "Account deleted.");
		}
		if (!in_array($action, ["add", "edit"], true)) throw new RuntimeException("Invalid action.");
		$original = trim($_POST["original_email"] ?? "");
		$first = trim($_POST["first_name"] ?? "");
		$last = trim($_POST["last_name"] ?? "");
		$dob = trim($_POST["dob"] ?? "");
		$gender = trim($_POST["gender"] ?? "");
		$hometown = trim($_POST["hometown"] ?? "");
		$type = trim($_POST["type"] ?? "");
		$password = $_POST["password"] ?? "";
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 50 ||
			!preg_match('/^[A-Za-z ]{1,50}$/', $first) || !preg_match('/^[A-Za-z ]{1,50}$/', $last) ||
			!in_array($gender, ["Male", "Female"], true) || $hometown === "" || strlen($hometown) > 50 ||
			!in_array($type, ["admin", "user"], true) ||
			($dob !== "" && (!($date = DateTime::createFromFormat('!Y-m-d', $dob)) || $date->format('Y-m-d') !== $dob)) ||
			($action === "add" && $password === "")) throw new RuntimeException("Please enter valid account details and a password for new accounts.");
		if ($action === "edit" && $original === $_SESSION["email"] && $type !== "admin") throw new RuntimeException("You cannot remove your own admin access.");
		$pdo->beginTransaction();
		if ($action === "add") {
			$statement = $pdo->prepare("INSERT INTO user_table (email, first_name, last_name, dob, gender, hometown) VALUES (?, ?, ?, ?, ?, ?)");
			$statement->execute([$email, $first, $last, $dob ?: null, $gender, $hometown]);
			$statement = $pdo->prepare("INSERT INTO account_table (email, password, type) VALUES (?, ?, ?)");
			$statement->execute([$email, password_hash($password, PASSWORD_DEFAULT), $type]);
		} else {
			$statement = $pdo->prepare("UPDATE user_table SET email=?, first_name=?, last_name=?, dob=?, gender=?, hometown=? WHERE email=?");
			$statement->execute([$email, $first, $last, $dob ?: null, $gender, $hometown, $original]);
			if (!$statement->rowCount() && $email !== $original) throw new RuntimeException("Account not found.");
			if ($password !== "") {
				$statement = $pdo->prepare("UPDATE account_table SET type=?, password=? WHERE email=?");
				$statement->execute([$type, password_hash($password, PASSWORD_DEFAULT), $email]);
			} else {
				$statement = $pdo->prepare("UPDATE account_table SET type=? WHERE email=?");
				$statement->execute([$type, $email]);
			}
			if ($original === $_SESSION["email"]) $_SESSION["email"] = $email;
		}
		$pdo->commit();
		adminRedirect("manage_accounts.php", "Account saved.");
	} catch (RuntimeException|PDOException $exception) {
		if ($pdo->inTransaction()) $pdo->rollBack();
		$error = $exception instanceof PDOException ? "Unable to save account. Check whether the email already exists." : $exception->getMessage();
	}
}
if (isset($_GET["edit"])) {
	$statement = $pdo->prepare("SELECT u.*, a.type FROM user_table u JOIN account_table a ON a.email=u.email WHERE u.email=?");
	$statement->execute([$_GET["edit"]]);
	$edit = $statement->fetch() ?: null;
}
$accounts = $pdo->query("SELECT u.email,u.first_name,u.last_name,a.type FROM user_table u JOIN account_table a ON a.email=u.email ORDER BY u.email")->fetchAll();
$notice = $_SESSION["admin_notice"] ?? "";
unset($_SESSION["admin_notice"]);
