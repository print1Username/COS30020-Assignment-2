<?php

function startSession(): void
{
	if ( session_status() !== PHP_SESSION_ACTIVE ) {
		session_start();
	}
}

function requireUser(bool $allowAdmin = false): void
{
	startSession();
	$allowedTypes = $allowAdmin ? [ "user", "admin" ] : [ "user" ];
	if ( ($_SESSION["logged_in"] ?? false) !== true ||
		!in_array($_SESSION["type"] ?? null, $allowedTypes, true) ||
		!isset($_SESSION["email"]) ) {
		header("Location: login.php");
		exit;
	}
}

function hasAdminSession(): bool
{
	startSession();
	return ($_SESSION["logged_in"] ?? false) === true &&
		($_SESSION["type"] ?? null) === "admin" &&
		is_string($_SESSION["email"] ?? null) &&
		$_SESSION["email"] !== "";
}

function adminAccountIsCurrent(PDO $pdo, string $email): bool
{
	$statement = $pdo -> prepare(
		"SELECT a.type FROM user_table AS u
		INNER JOIN account_table AS a ON a.email = u.email
		WHERE u.email = ? LIMIT 1"
	);
	$statement -> execute([ $email ]);
	return $statement -> fetchColumn() === "admin";
}

function invalidateCurrentSession(): void
{
	$_SESSION = [];
	if ( ini_get("session.use_cookies") ) {
		$cookieParameters = session_get_cookie_params();
		$cookieOptions = [
			"expires" => time() - 42000,
			"path" => $cookieParameters["path"],
			"domain" => $cookieParameters["domain"],
			"secure" => $cookieParameters["secure"],
			"httponly" => $cookieParameters["httponly"]
		];
		if ( !empty($cookieParameters["samesite"]) ) {
			$cookieOptions["samesite"] = $cookieParameters["samesite"];
		}
		setcookie(session_name(), "", $cookieOptions);
	}
	session_destroy();
}

function requireAdmin(): void
{
	startSession();
	if ( !hasAdminSession() ) {
		header("Location: login.php");
		exit;
	}
	global $pdo;
	if ( !isset($pdo) || !($pdo instanceof PDO) ) {
		throw new LogicException("Admin authorization requires a database connection.");
	}
	if ( !adminAccountIsCurrent($pdo, $_SESSION["email"]) ) {
		invalidateCurrentSession();
		header("Location: login.php");
		exit;
	}
}
